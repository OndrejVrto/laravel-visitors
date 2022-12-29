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
     * @return Collection<int,ListOptionData>
     */
    public static function prepare(
        string $tableName,
        bool $generateCategoryStatistics,
        bool $generateCrawlersStatistics,
        int $lastId,
    ): Collection {
        // aply configuration
        $range = [["id, viewable_type, viewable_id", "id, viewable_type, NULL", "id, NULL, NULL"]];
        $columns = ["viewable_type", "viewable_id"];

        if ($generateCategoryStatistics) {
            $range[] = ["category", "NULL"];
            $columns[] = "category";
        }

        if ($generateCrawlersStatistics) {
            $range[] = ["is_crawler", "NULL"];
            $columns[] = "is_crawler";
        }

        // generate list of posibilities
        $unionSubQuery = collect(combinations($range, ", "))
            ->map(fn ($i) => "SELECT ".$i." FROM `".$tableName."`")
            ->implode(" UNION ");

        // fetch query
        return DB::table(DB::raw("({$unionSubQuery}) AS VARIANTS"))
            ->distinct()
            ->select($columns)
            ->where("id", "<=", $lastId)
            ->orderByRaw(implode(", ", $columns))
            ->get()
            ->map(fn($item) => (array) $item)
            ->map(function($item) {
                $is_crawler = array_key_exists("is_crawler", $item) ? (is_null($item["is_crawler"]) ? null : (bool) $item["is_crawler"] ) : false;
                $category   = array_key_exists("category", $item) ? (is_null($item["category"]) ? null : (int) $item["category"] ) : null;

                return new ListOptionData(
                    $item["viewable_type"],
                    $item["viewable_id"],
                    $is_crawler,
                    $category
                );
            });
    }
}
