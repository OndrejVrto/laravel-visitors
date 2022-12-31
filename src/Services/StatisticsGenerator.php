<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Models\VisitorsExpires;
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
        $this->prepareTables();

        dispatch(new GenerateTotalGraphJob($this->configuration, new ListPossibleQueriesData()));

        (new ListPossibleQueries($this->configuration))
            ->get()
            ->chunk(12)
            ->each(function ($list): void {
                dispatch(new GenerateDailyGraphJob($this->configuration, $list));
            });
    }

    private function handleConfiguration(): StatisticsConfigData {
        $visitorData = new VisitorsData();

        $visitorData
            ->query()
            ->selectRaw("max(`id`) as `last_id`")
            ->selectRaw("max(`visited_at`) as `date_to`")
            ->selectRaw("min(`visited_at`) as `date_from`")
            ->firstOrFail();

        $to = $visitorData->getAttributeValue('date_to');
        $from = $visitorData->getAttributeValue('date_from');
        $lastId = $visitorData->getAttributeValue('last_id');

        $crawlerStatistics = config('visitors.create_crawlers_statistics');
        $categoryStatistics = config('visitors.create_categories_statistics');

        return new StatisticsConfigData(
            numberDaysStatistics      : $visitorData->numberDaysStatistics(),
            dbConnectionName          : $visitorData->getConnectionName() ?? 'mysql',
            dataTableName             : $visitorData->getTable(),
            graphTableName            : (new VisitorsDailyGraph())->getTable(),
            statisticsTableName       : (new VisitorsStatistics())->getTable(),
            to                        : ($to instanceof Carbon) ? $to : Carbon::now(),
            from                      : ($from instanceof Carbon) ? $from : Carbon::now()->subYear(),
            lastId                    : is_int($lastId) ? $lastId : 1,
            generateCrawlersStatistics: is_bool($crawlerStatistics) && $crawlerStatistics,
            generateCategoryStatistics: is_bool($categoryStatistics) && $categoryStatistics,
        );
    }

    private function prepareTables(): void {
        Artisan::call('model:prune', [
            '--model' => [
                VisitorsData::class,
                VisitorsExpires::class
            ],
        ]);

        DB::connection($this->configuration->dbConnectionName)
            ->table($this->configuration->statisticsTableName)
            ->truncate();

        DB::connection($this->configuration->dbConnectionName)
            ->table($this->configuration->graphTableName)
            ->truncate();
    }
}
