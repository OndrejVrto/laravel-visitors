<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\DTO\StatisticsConfigData;
use OndrejVrto\Visitors\Models\VisitorsDailyGraph;
use OndrejVrto\Visitors\Models\VisitorsStatistics;
use OndrejVrto\Visitors\Jobs\GenerateDailyGraphJob;
use OndrejVrto\Visitors\Jobs\GenerateTotalGraphJob;
use OndrejVrto\Visitors\DTO\ListPossibleQueriesData;

class StatisticsGenerator {
    private readonly StatisticsConfigData $configuration;

    public function __construct() {
        $this->configuration = $this->handleConfiguration();
    }

    public function run(): void {
        $this->truncateTables();

        dispatch(new GenerateTotalGraphJob($this->configuration, new ListPossibleQueriesData()));

        (new ListPossibleQueries($this->configuration))
            ->get()
            ->chunk(12)
            ->each(function ($list) {
                dispatch(new GenerateDailyGraphJob($this->configuration, $list));
            });
    }

    public static function numberDaysStatistics(): int {
        $days = config('visitors.number_days_statistics');
        return is_int($days) && $days >= 1 && $days <= 36500
            ? $days
            : 730;
    }

    private function handleConfiguration(): StatisticsConfigData {
        $range = VisitorsData::query()
            ->selectRaw("min(`visited_at`) as `date_from`")
            ->selectRaw("max(`visited_at`) as `date_to`")
            ->selectRaw("max(`id`) as `last_id`")
            ->firstOrFail();

        $from = $range->getAttributeValue('date_from');
        $to = $range->getAttributeValue('date_to');
        $lastId = $range->getAttributeValue('last_id');

        $crawlerStatistics = config('visitors.create_crawlers_statistics');
        $categoryStatistics = config('visitors.create_categories_statistics');

        return new StatisticsConfigData(
            lastId                    : is_int($lastId) ? $lastId : 1,
            numberDaysStatistics      : static::numberDaysStatistics(),
            dataTableName             : (new VisitorsData())->getTable(),
            graphTableName            : (new VisitorsDailyGraph())->getTable(),
            statisticsTableName       : (new VisitorsStatistics())->getTable(),
            dbConnectionName          : (new VisitorsData())->getConnectionName(),
            to                        : ($to instanceof Carbon) ? $to : Carbon::now(),
            from                      : ($from instanceof Carbon) ? $from : Carbon::now()->subYear(),
            generateCrawlersStatistics: is_bool($crawlerStatistics) && $crawlerStatistics,
            generateCategoryStatistics: is_bool($categoryStatistics) && $categoryStatistics,
        );
    }

    private function truncateTables(): void {
        DB::connection($this->configuration->dbConnectionName)
            ->table($this->configuration->statisticsTableName)
            ->truncate();

        DB::connection($this->configuration->dbConnectionName)
            ->table($this->configuration->graphTableName)
            ->truncate();
    }
}
