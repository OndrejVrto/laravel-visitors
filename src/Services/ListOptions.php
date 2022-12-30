<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use OndrejVrto\Visitors\DTO\ListOptionData;

class ListOptions {
    /**
     * TODO: desc
     *
     * @param string $tableName
     * @param boolean $generateCategoryStatistics
     * @param boolean $generateCrawlersStatistics
     * @param integer $lastId
     *
     * @return Collection<int,ListOptionData>
     */
    public static function prepare(
        ?string $nameConnection,
        string $tableName,
        bool $generateCategoryStatistics,
        bool $generateCrawlersStatistics,
        int $lastId,
    ): Collection {
        // aply configuration
        $range = [["id, viewable_type, viewable_id", "id, viewable_type, NULL", "id, NULL, NULL"]];
        $columns = ["viewable_type", "viewable_id"];

        if ($generateCrawlersStatistics) {
            $range[] = ["is_crawler", "NULL"];
            $columns[] = "is_crawler";
        }

        if ($generateCategoryStatistics) {
            $range[] = ["category", "NULL"];
            $columns[] = "category";
        }

        // generate list of posibilities
        $unionSubQuery = collect(combinations($range, ", "))
            ->map(function ($i) use ($tableName) {
                $i = is_array($i) ? implode(", ", $i) : $i;
                return "SELECT ".$i." FROM `".$tableName."`";
            })->implode(" UNION ");

        // fetch query
        return DB::connection($nameConnection)
            ->query()
            ->select($columns)
            ->distinct()
            ->fromSub($unionSubQuery, 'VARIANTS')
            ->where("id", "<=", $lastId)
            // ->orderByRaw(implode(", ", $columns))
            ->get()
            // ->dump()
            ->map(function ($item) {
                $item = (object) $item;
                $viewable_type = property_exists($item, "viewable_type")
                    ? (is_null($item->viewable_type) ? null : (string) $item->viewable_type)
                    : null;
                $viewable_id = property_exists($item, "viewable_id")
                    ? (is_null($item->viewable_id) ? null : (int) $item->viewable_id)
                    : null;
                $is_crawler = property_exists($item, "is_crawler")
                    ? (is_null($item->is_crawler) ? null : (bool) $item->is_crawler)
                    : false;
                $category = property_exists($item, "category")
                    ? (is_null($item->category) ? null : (int) $item->category)
                    : null;

                return new ListOptionData(
                    $viewable_type,
                    $viewable_id,
                    $is_crawler,
                    $category
                );
            });
        // ->dd();
    }
}
