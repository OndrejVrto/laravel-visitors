<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Jobs;

use DateTimeImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use OndrejVrto\LineChart\LineChart;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Data\GraphProperties;
use OndrejVrto\Visitors\Models\VisitorsTraffic;
use OndrejVrto\Visitors\Data\StatisticsConfigData;
use OndrejVrto\Visitors\Data\ListPossibleQueriesData;

class GenerateTrafficJob implements ShouldQueue {
    use Queueable;
    use Dispatchable;
    use SerializesModels;
    use InteractsWithQueue;

    private const MISSING_VALUE = 0;

    /**
     * @param Collection<int,ListPossibleQueriesData> $listPossibleQueries
     */
    public function __construct(
        private readonly Collection $listPossibleQueries,
        private readonly GraphProperties $graphProperties,
        private readonly StatisticsConfigData $configuration,
    ) {
    }

    public function handle(): void {
        $data = [];
        foreach ($this->listPossibleQueries as $queryData) {
            $visitCount = $this->visitCount($queryData);

            $dailyNumbers = $this->dailyNumbers($visitCount);

            $data[] = [
                'viewable_type'           => $queryData->viewable_type,
                'viewable_id'             => $queryData->viewable_id,
                'category'                => $queryData->category,
                'is_crawler'              => $queryData->is_crawler,
                'daily_visits'            => $dailyNumbers,
                'svg_graph'               => $this->getSvgChart($dailyNumbers->values()->all()),
                'day_maximum'             => $this->calculateDayMaximumCount($dailyNumbers),
                'visit_total'             => $this->calculateTotalCount($dailyNumbers),
                'visit_last_1_day'        => $this->calculateLast1dayCount($dailyNumbers),
                'visit_last_7_days'       => $this->calculateLast7daysCount($dailyNumbers),
                'visit_last_30_days'      => $this->calculateLast30daysCount($dailyNumbers),
                'visit_last_365_days'     => $this->calculateLast365daysCount($dailyNumbers),
                'sumar_languages'         => $this->sumarQuery($queryData, 'language', 'lang'),
                'sumar_operating_systems' => $this->sumarQuery($queryData, 'operating_system', 'os'),
            ];
        }

        VisitorsTraffic::insert($data);
    }

    /** @return Collection<string,array<string,int|string>> $visitCount */
    private function visitCount(ListPossibleQueriesData $listOptionData): Collection {
        return VisitorsData::query()
            ->selectRaw("date(`visited_at`) as `visits_date`")
            ->selectRaw("count(*) as `visits_count`")
            ->where('data_id', "<=", $this->configuration->lastId)
            ->unless(null === $listOptionData->viewable_type, fn ($q) => $q->where("viewable_type", $listOptionData->viewable_type))
            ->unless(null === $listOptionData->viewable_id, fn ($q) => $q->where("viewable_id", $listOptionData->viewable_id))
            ->unless(null === $listOptionData->is_crawler, fn ($q) => $q->where("is_crawler", $listOptionData->is_crawler))
            ->unless(null === $listOptionData->category, fn ($q) => $q->where("category", $listOptionData->category))
            ->groupBy("visits_date")
            ->orderByDesc('visits_date')
            ->get()
            ->keyBy('visits_date');
    }

    /** @param  Collection<string,array<string,int|string>> $visitCount */
    private function dailyNumbers(Collection $visitCount): Collection {
        return (new Collection())
            ->range(0, $this->configuration->numberDaysStatistics)
            ->map(fn (int $days) => (new DateTimeImmutable("-{$days} days"))->format('Y-m-d'))
            ->mapWithKeys(fn (string $key) => [$key => $visitCount->get($key)['visits_count'] ?? self::MISSING_VALUE]);
    }

    private function sumarQuery(ListPossibleQueriesData $listOptionData, string $columnName, string $alias): Collection {
        return VisitorsData::query()
            ->selectRaw("`{$columnName}` as `{$alias}`, count(*) as `count`")
            ->where('data_id', "<=", $this->configuration->lastId)
            ->unless(null === $listOptionData->viewable_type, fn ($q) => $q->where("viewable_type", $listOptionData->viewable_type))
            ->unless(null === $listOptionData->is_crawler, fn ($q) => $q->where("is_crawler", $listOptionData->is_crawler))
            ->unless(null === $listOptionData->category, fn ($q) => $q->where("category", $listOptionData->category))
            ->groupBy($alias)
            ->orderByDesc("count")
            ->get();
    }

    /** @param array<int,mixed> $values */
    private function getSvgChart(array $values): ?string {
        if ( ! $this->configuration->generateGraphs) {
            return null;
        }

        return LineChart::new(...$values)
            ->withColorGradient(...$this->graphProperties->colors)
            ->withDimensions($this->graphProperties->width_svg, $this->graphProperties->height_svg)
            ->withLockYAxisRange($this->graphProperties->maximum_value_lock)
            ->withMaxItemAmount($this->graphProperties->maximum_days)
            ->withStrokeWidth($this->graphProperties->stroke_width)
            ->withOrderReversed($this->graphProperties->order_reverse)
            ->make();
    }

    private function calculateDayMaximumCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->max());
    }

    private function calculateTotalCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->sum());
    }

    private function calculateLast1dayCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->slice(1, 1)->first());
    }

    private function calculateLast7daysCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->take(7)->sum());
    }

    private function calculateLast30daysCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->take(30)->sum());
    }

    private function calculateLast365daysCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->take(365)->sum());
    }
}
