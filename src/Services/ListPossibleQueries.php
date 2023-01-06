<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use OndrejVrto\Visitors\Data\StatisticsConfigData;
use OndrejVrto\Visitors\Data\ListPossibleQueriesData;

class ListPossibleQueries {
    public function __construct(
        private readonly StatisticsConfigData $configuration,
        private readonly bool $typeForTraffik = true,
    ) {
    }

    /**
     * @return Collection<int,ListPossibleQueriesData>
     */
    public function get(): Collection {
        return DB::connection($this->configuration->dbConnectionName)
            ->query()
            ->select($this->columnNames())
            ->distinct()
            ->fromSub($this->unionQuery(), 'variants')
            ->where('id', '<=', $this->configuration->lastId)
            ->orderBy('viewable_type')
            ->when($this->typeForTraffik, fn ($q) => $q->orderBy('viewable_id'))
            ->when($this->configuration->generateCrawlersStatistics, fn ($q) => $q->orderBy('is_crawler'))
            ->when($this->configuration->generateCategoryStatistics, fn ($q) => $q->orderBy('category'))
            ->get()
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
    }

    private function unionQuery(): string {
        return $this->possibleCombinationColumn()
            ->map(function ($columnsString): string {
                if (!is_string($columnsString)) {
                    return '';
                }

                return sprintf(
                    'select %s from `%s`',
                    $columnsString,
                    $this->configuration->dataTableName
                );
            })
            ->implode(' union ');
    }

    /**
     * @return Collection<string>
     */
    private function possibleCombinationColumn(): Collection {
        $range = $this->typeForTraffik
            ? [["`id`, `viewable_type`, `viewable_id`",]]
            : [["`id`, `viewable_type`", "`id`, null"]];

        if ($this->configuration->generateCrawlersStatistics) {
            $range[] = ['`is_crawler`', 'null'];
        }

        if ($this->configuration->generateCategoryStatistics) {
            $range[] = ['`category`', 'null'];
        }

        $combinationRange = combinations($range);

        return collect($combinationRange)
            ->map(fn ($col) => is_array($col) ? implode(', ', $col) : $col);
    }

    /**
     * @return string[]
     */
    private function columnNames(): array {
        $columns = $this->typeForTraffik
            ? ['viewable_type', 'viewable_id']
            : ['viewable_type'];

        if ($this->configuration->generateCrawlersStatistics) {
            $columns[] = 'is_crawler';
        }

        if ($this->configuration->generateCategoryStatistics) {
            $columns[] = 'category';
        }

        return $columns;
    }
}
