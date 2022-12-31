<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use OndrejVrto\Visitors\DTO\StatisticsConfigData;
use OndrejVrto\Visitors\DTO\ListPossibleQueriesData;

class ListPossibleQueries {
    public function __construct(
        private readonly StatisticsConfigData $configuration,
    ) {
    }

    /**
     * @return Collection<int,ListPossibleQueriesData>
     */
    public function get(): Collection {
        return DB::connection($this->configuration->dbConnectionName)
            ->query()
            ->select($this->getColumnNames())
            ->distinct()
            ->fromSub($this->getUnionQuery(), 'variants')
            ->where('id', '<=', $this->configuration->lastId)
            ->orderBy('viewable_type')
            ->orderBy('viewable_id')
            ->when($this->configuration->generateCrawlersStatistics, fn ($q) => $q->orderBy('is_crawler'))
            ->when($this->configuration->generateCategoryStatistics, fn ($q) => $q->orderBy('category'))
            // ->dd()
            ->get()
            // ->dump()
            ->map(function ($item): ListPossibleQueriesData {
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

                return new ListPossibleQueriesData(
                    $viewable_type,
                    $viewable_id,
                    $is_crawler,
                    $category
                );
            });
        // ->dd();
    }

    private function getUnionQuery(): string {
        return collect(
            combinations($this->getRangeNames(), ", ")
        )->map(function ($i): string {
            $i = is_array($i) ? implode(", ", $i) : $i;
            return "select {$i} from `{$this->configuration->dataTableName}`";
        })->implode(" union ");
    }

    /**
     * @return string[]
     */
    private function getRangeNames(): array {
        $range = [[
            "`id`, `viewable_type`, `viewable_id`",
            "`id`, `viewable_type`, null",
            "`id`, null, null"
        ]];

        if ($this->configuration->generateCrawlersStatistics) {
            $range[] = ["`is_crawler`", "null"];
        }

        if ($this->configuration->generateCategoryStatistics) {
            $range[] = ["`category`", "null"];
        }

        return $range;
    }

    /**
     * @return string[]
     */
    private function getColumnNames(): array {
        $columns = ["viewable_type", "viewable_id"];

        if ($this->configuration->generateCrawlersStatistics) {
            $columns[] = "is_crawler";
        }

        if ($this->configuration->generateCategoryStatistics) {
            $columns[] = "category";
        }

        return $columns;
    }
}
