<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Models\VisitorsTraffic;
use OndrejVrto\Visitors\Jobs\GenerateTraffikJob;
use OndrejVrto\Visitors\Traits\VisitorsSettings;
use OndrejVrto\Visitors\DTO\StatisticsConfigData;
use OndrejVrto\Visitors\Models\VisitorsStatistics;
use OndrejVrto\Visitors\Jobs\GenerateStatisticsJob;

class StatisticsGenerator {
    use VisitorsSettings;

    private StatisticsConfigData $configuration;

    public function __construct() {
        $this->configuration = $this->handleConfiguration();
    }

    public function run(): int {
        $this->prepareTables();

        $statistic = (new ListPossibleQueries($this->configuration, false))->get();
        $statistic
            ->chunk(20)
            ->each(function ($list): void {
                dispatch(new GenerateStatisticsJob($this->configuration, $list));
            });

        $traffic = (new ListPossibleQueries($this->configuration, true))->get();
        $traffic
            ->chunk(50)
            ->each(function ($list): void {
                dispatch(new GenerateTraffikJob($this->configuration, $list));
            });

        return $statistic->count() + $traffic->count();
    }

    private function handleConfiguration(): StatisticsConfigData {
        $visitorData = new VisitorsData();

        $range = $visitorData
            ->query()
            ->selectRaw("max(`id`) as `last_id`")
            ->selectRaw("max(`visited_at`) as `date_to`")
            ->selectRaw("min(`visited_at`) as `date_from`")
            ->firstOrFail();

        $to = $range->getAttributeValue('date_to');
        $from = $range->getAttributeValue('date_from');
        $lastId = $range->getAttributeValue('last_id');

        $days = $this->numberDaysStatistics();

        return new StatisticsConfigData(
            numberDaysStatistics      : $days,
            dbConnectionName          : $visitorData->getConnectionName() ?? 'mysql',
            dataTableName             : $visitorData->getTable(),
            graphTableName            : (new VisitorsTraffic())->getTable(),
            statisticsTableName       : (new VisitorsStatistics())->getTable(),
            to                        : ($to instanceof Carbon) ? $to : Carbon::now(),
            from                      : ($from instanceof Carbon) ? $from : Carbon::now()->subDays($days),
            lastId                    : is_int($lastId) ? $lastId : 1,
            generateCrawlersStatistics: $this->trafficForCrawlersAndPersons(),
            generateCategoryStatistics: $this->trafficForCategories(),
        );
    }

    private function prepareTables(): void {
        Artisan::call('model:prune', ['--model' => VisitorsData::class]);

        DB::connection($this->configuration->dbConnectionName)
            ->table($this->configuration->statisticsTableName)
            ->truncate();

        DB::connection($this->configuration->dbConnectionName)
            ->table($this->configuration->graphTableName)
            ->truncate();
    }
}
