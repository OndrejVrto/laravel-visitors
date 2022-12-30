<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Services;

use Illuminate\Support\Collection;
use Illuminate\Database\Connection;
use OndrejVrto\Visitors\DTO\ListOptionData;

class ListOptions {
    /**
     * TODO: desc
     *
     * @return Collection<int,ListOptionData>
     */
    public static function prepare(
        Connection $dbConnection,
        string $tableName,
        bool $generateCategoryStatistics,
        bool $generateCrawlersStatistics,
        int $lastId,
    ): Collection {
        $columns = ["viewable_type", "viewable_id"];
        $range = [[
            "`id`, `viewable_type`, `viewable_id`",
            "`id`, `viewable_type`, null",
            "`id`, null, null"
        ]];

        if ($generateCrawlersStatistics) {
            $columns[] = "is_crawler";
            $range[] = ["`is_crawler`", "null"];
        }

        if ($generateCategoryStatistics) {
            $columns[] = "category";
            $range[] = ["`category`", "null"];
        }

        // generate list of posibilities
        $unionSubQuery = collect(
                combinations($range, ", ")
            )->map(function ($i) use ($tableName): string {
                $i = is_array($i) ? implode(", ", $i) : $i;
                return "select $i from `$tableName`";
            })->implode(" union ");

        // fetch query
        return $dbConnection
            ->query()
            ->select($columns)
            ->distinct()
            ->fromSub($unionSubQuery, 'variants')
            ->where('id', '<=', $lastId)
            ->orderBy('viewable_type')
            ->orderBy('viewable_id')
            ->when($generateCrawlersStatistics, fn($q) => $q->orderBy('is_crawler'))
            ->when($generateCategoryStatistics, fn($q) => $q->orderBy('category'))
            // ->dd()
            ->get()
            // ->dump()
            ->map(function ($item): ListOptionData {
                $item = (object) $item;
                $viewable_type = property_exists($item, 'viewable_type')
                    ? (is_null($item->viewable_type) ? null : (string) $item->viewable_type)
                    : null;
                $viewable_id = property_exists($item, 'viewable_id')
                    ? (is_null($item->viewable_id) ? null : (int) $item->viewable_id)
                    : null;
                $is_crawler = property_exists($item, 'is_crawler')
                    ? (is_null($item->is_crawler) ? null : (bool) $item->is_crawler)
                    : false;
                $category = property_exists($item, 'category')
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
