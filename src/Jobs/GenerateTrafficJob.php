<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use OndrejVrto\LineChart\LineChart;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use OndrejVrto\Visitors\Data\GraphAppearance;
use OndrejVrto\Visitors\Models\VisitorsTraffic;
use OndrejVrto\Visitors\Data\StatisticsConfigData;
use OndrejVrto\Visitors\Traits\CalculateStatistics;
use OndrejVrto\Visitors\Data\ListPossibleQueriesData;
use OndrejVrto\Visitors\Builder\StatisticsQueriesBuilder;

class GenerateTrafficJob implements ShouldQueue {
    use Queueable;
    use Dispatchable;
    use SerializesModels;
    use InteractsWithQueue;
    use CalculateStatistics;

    /**
     * @param Collection<int,ListPossibleQueriesData> $listPossibleQueries
     */
    public function __construct(
        private readonly StatisticsConfigData $configuration,
        private readonly GraphAppearance $graphAppearance,
        private readonly Collection $listPossibleQueries,
    ) {
    }

    public function handle(): void {
        $queryBuilder = new StatisticsQueriesBuilder($this->configuration);
        $dateQuery = $queryBuilder->dateRangeQuery();

        $data = [];
        foreach ($this->listPossibleQueries as $queryData) {
            $dailyVisitQuery = $queryBuilder->visitQuery($queryData);
            $dailyNumbersQuery = $queryBuilder->dailyNumbersQuery($dateQuery, $dailyVisitQuery);
            $dailyNumbers = $dailyNumbersQuery->get();

            $dayMax = $this->calculateDayMaximumCount($dailyNumbers);
            $graphSvg = $this->configuration->generateGraphs
                ? LineChart::new($dailyNumbers->pluck('visits_count'))
                    ->withColorGradient(...$this->graphAppearance->colors)
                    ->withDimensions($this->graphAppearance->width_svg, $this->graphAppearance->height_svg)
                    ->withLockYAxisRange($this->graphAppearance->maximum_value_lock)
                    ->withMaxItemAmount($this->graphAppearance->maximum_days)
                    ->withOrderReversed($this->graphAppearance->order_reverse)
                    ->withStrokeWidth($this->graphAppearance->stroke_width)
                    ->make()
                : null;

            $data[] = [
                'viewable_type'       => $queryData->viewable_type,
                'viewable_id'         => $queryData->viewable_id,
                'category'            => $queryData->category,
                'is_crawler'          => $queryData->is_crawler,
                'daily_numbers'       => $dailyNumbers,
                'day_maximum'         => $dayMax,
                'svg_graph'           => $graphSvg,
                'visit_total'         => $this->calculateTotalCount($dailyNumbers),
                'visit_last_1_day'    => $this->calculateLast1dayCount($dailyNumbers),
                'visit_last_7_days'   => $this->calculateLast7daysCount($dailyNumbers),
                'visit_last_30_days'  => $this->calculateLast30daysCount($dailyNumbers),
                'visit_last_365_days' => $this->calculateLast365daysCount($dailyNumbers),
            ];
        }

        VisitorsTraffic::query()
            ->insert($data);
    }
}
