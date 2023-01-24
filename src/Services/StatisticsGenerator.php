<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Data\GraphAppearance;
use OndrejVrto\Visitors\Models\VisitorsTraffic;
use OndrejVrto\Visitors\Jobs\GenerateTrafficJob;
use OndrejVrto\Visitors\Traits\VisitorsSettings;
use OndrejVrto\Visitors\Data\StatisticsConfigData;
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

        $formatGraph = $this->resolveGraphApperance('statistics');
        $statistic = (new ListPossibleQueries($this->configuration, false))->get();
        $statistic
            ->chunk(20)
            ->each(function ($list) use ($formatGraph): void {
                dispatch(new GenerateStatisticsJob(
                    $this->configuration,
                    $formatGraph,
                    $list
                ));
            });

        $formatGraph = $this->resolveGraphApperance('traffic');
        $traffic = (new ListPossibleQueries($this->configuration, true))->get();
        $traffic
            ->chunk(50)
            ->each(function ($list) use ($formatGraph): void {
                dispatch(new GenerateTrafficJob(
                    $this->configuration,
                    $formatGraph,
                    $list
                ));
            });

        return $statistic->count() + $traffic->count();
    }

    private function handleConfiguration(): StatisticsConfigData {
        $visitorData = new VisitorsData();

        $range = $visitorData
            ->query()
            ->selectRaw("max(`data_id`) as `last_id`")
            ->selectRaw("max(`visited_at`) as `date_to`")
            ->selectRaw("min(`visited_at`) as `date_from`")
            ->firstOrFail();

        $to = $range->getAttributeValue('date_to');
        $from = $range->getAttributeValue('date_from');
        $lastId = $range->getAttributeValue('last_id');

        $days = $this->numberDaysStatistics();

        return new StatisticsConfigData(
            numberDaysStatistics      : $days,
            dbConnectionName          : $visitorData->getConnectionName(),
            dataTableName             : $visitorData->getTable(),
            graphTableName            : (new VisitorsTraffic())->getTable(),
            statisticsTableName       : (new VisitorsStatistics())->getTable(),
            to                        : ($to instanceof Carbon) ? $to : Carbon::now(),
            from                      : ($from instanceof Carbon) ? $from : Carbon::now()->subDays($days),
            lastId                    : is_int($lastId) ? $lastId : 1,
            generateCrawlersStatistics: $this->trafficForCrawlersAndPersons(),
            generateCategoryStatistics: $this->trafficForCategories(),
            generateGraphs            : $this->defaultGenerateGraphs(),
        );
    }

    private function resolveGraphApperance(string $type = 'traffic'): GraphAppearance {
        return new GraphAppearance(
            colors            : $this->graphColors($type),
            width_svg         : $this->graphWidthSvg($type),
            height_svg        : $this->graphHeighthSvg($type),
            stroke_width      : $this->graphStrokeWidth($type),
            maximum_days      : $this->graphMaximumDays($type),
            order_reverse     : $this->graphOrderReversed($type),
            maximum_value_lock: $this->graphMaximumValue($type),
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
