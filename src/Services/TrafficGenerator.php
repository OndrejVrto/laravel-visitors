<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Models\VisitorsInfo;
use OndrejVrto\Visitors\Data\GraphProperties;
use OndrejVrto\Visitors\Models\VisitorsTraffic;
use OndrejVrto\Visitors\Jobs\GenerateTrafficJob;
use OndrejVrto\Visitors\Traits\VisitorsSettings;
use OndrejVrto\Visitors\Data\StatisticsConfigData;

class TrafficGenerator {
    use VisitorsSettings;

    private StatisticsConfigData $configuration;
    private GraphProperties $formatGraph;

    public function __construct() {
        $this->configuration = $this->resolveConfiguration();
        $this->formatGraph = $this->resolveGraphProperties();
        $this->prepareTables();
    }

    public function run(): int {
        $queriesCount = (new ListPossibleQueries($this->configuration))
            ->get()
            ->chunk(20)
            ->each(function ($list): void {
                dispatch(new GenerateTrafficJob(
                    listPossibleQueries: $list,
                    graphProperties: $this->formatGraph,
                    configuration: $this->configuration,
                ));
            })
            ->collapse()
            ->count();

        VisitorsInfo::create([
            'count_rows'   => $queriesCount,
            'from'         => $this->configuration->from,
            'to'           => $this->configuration->to,
            'last_data_id' => $this->configuration->lastId,
            // 'updated_at'   => Carbon::now(),
        ]);

        return $queriesCount;
    }

    private function resolveConfiguration(): StatisticsConfigData {
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
            lastId                    : is_int($lastId) ? $lastId : 1,
            numberDaysStatistics      : $days,
            generateCategoryStatistics: $this->trafficForCategories(),
            generateCrawlersStatistics: $this->trafficForCrawlersAndPersons(),
            generateGraphs            : $this->defaultGenerateGraphs(),
            from                      : ($from instanceof Carbon) ? $from : Carbon::now()->subDays($days),
            to                        : ($to instanceof Carbon) ? $to : Carbon::now(),
            dataTableName             : $visitorData->getTable(),
        );
    }

    private function resolveGraphProperties(): GraphProperties {
        return new GraphProperties(
            colors            : $this->graphColors(),
            width_svg         : $this->graphWidthSvg(),
            height_svg        : $this->graphHeighthSvg(),
            stroke_width      : $this->graphStrokeWidth(),
            maximum_days      : $this->graphMaximumDays(),
            maximum_value_lock: $this->graphMaximumValue(),
            order_reverse     : $this->graphOrderReversed(),
        );
    }

    private function prepareTables(): void {
        Artisan::call('model:prune', ['--model' => VisitorsData::class]);

        VisitorsTraffic::truncate();
    }
}
