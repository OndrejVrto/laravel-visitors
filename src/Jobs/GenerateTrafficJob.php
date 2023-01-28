<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use OndrejVrto\LineChart\LineChart;
use Illuminate\Database\Query\Builder;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use OndrejVrto\Visitors\Data\GraphProperties;
use OndrejVrto\Visitors\Models\VisitorsTraffic;
use OndrejVrto\Visitors\Data\StatisticsConfigData;
use OndrejVrto\Visitors\Data\ListPossibleQueriesData;

class GenerateTrafficJob implements ShouldQueue {
    use Queueable;
    use Dispatchable;
    use SerializesModels;
    use InteractsWithQueue;

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
        $dateQuery = $this->dateRangeQuery();

        $data = [];
        foreach ($this->listPossibleQueries as $queryData) {
            $dailyVisitQuery = $this->visitQuery($queryData);

            $dailyNumbers = $this->dailyNumbersQuery($dateQuery, $dailyVisitQuery)->get();

            $data[] = [
                'viewable_type'           => $queryData->viewable_type,
                'viewable_id'             => $queryData->viewable_id,
                'category'                => $queryData->category,
                'is_crawler'              => $queryData->is_crawler,
                'daily_numbers'           => $dailyNumbers,
                'day_maximum'             => $this->calculateDayMaximumCount($dailyNumbers),
                'svg_graph'               => $this->getSvgChart($dailyNumbers->pluck('visits_count')->all()),
                'visit_total'             => $this->calculateTotalCount($dailyNumbers),
                'visit_last_1_day'        => $this->calculateLast1dayCount($dailyNumbers),
                'visit_last_7_days'       => $this->calculateLast7daysCount($dailyNumbers),
                'visit_last_30_days'      => $this->calculateLast30daysCount($dailyNumbers),
                'visit_last_365_days'     => $this->calculateLast365daysCount($dailyNumbers),
                'sumar_languages'         => $this->sumarQuery($queryData, 'language', 'lang')->get(),
                'sumar_operating_systems' => $this->sumarQuery($queryData, 'operating_system', 'os')->get(),
            ];
        }

        VisitorsTraffic::query()
            ->insert($data);
    }

    private function sumarQuery(ListPossibleQueriesData $listOptionData, string $columnName, string $alias): Builder {
        return DB::connection()
            ->query()
            ->selectRaw("`{$columnName}` as `{$alias}`, count(*) as `count`")
            ->from($this->configuration->dataTableName)
            ->where('data_id', "<=", $this->configuration->lastId)
            ->unless(null === $listOptionData->viewable_type, fn ($q) => $q->where("viewable_type", $listOptionData->viewable_type))
            ->unless(null === $listOptionData->is_crawler, fn ($q) => $q->where("is_crawler", $listOptionData->is_crawler))
            ->unless(null === $listOptionData->category, fn ($q) => $q->where("category", $listOptionData->category))
            ->groupBy($alias)
            ->orderByDesc("count");
    }

    private function dateRangeQuery(): Builder {
        $dateRangeQuery = "select adddate('2022-01-01', t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) as `date` from
            (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
            (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
            (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2,
            (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t3";
        // $dateRangeQuery = trim(preg_replace('/\s\s+/', ' ', $dateRangeQuery));

        return DB::connection()
            ->query()
            ->fromSub($dateRangeQuery, 'x')
            ->whereRaw("`date` between subdate(curdate(), interval ? day) and curdate()", [$this->configuration->numberDaysStatistics]);
    }

    private function visitQuery(ListPossibleQueriesData $listOptionData): Builder {
        return DB::connection()
            ->query()
            ->selectRaw("date(`visited_at`) as `visits_date`")
            ->selectRaw("count(*) as `visits_count`")
            ->from($this->configuration->dataTableName)
            ->where('data_id', "<=", $this->configuration->lastId)
            ->unless(null === $listOptionData->viewable_type, fn ($q) => $q->where("viewable_type", $listOptionData->viewable_type))
            ->unless(null === $listOptionData->viewable_id, fn ($q) => $q->where("viewable_id", $listOptionData->viewable_id))
            ->unless(null === $listOptionData->is_crawler, fn ($q) => $q->where("is_crawler", $listOptionData->is_crawler))
            ->unless(null === $listOptionData->category, fn ($q) => $q->where("category", $listOptionData->category))
            ->groupBy("visits_date")
            ->orderByDesc('visits_date');
    }

    private function dailyNumbersQuery(Builder $dateQuery, Builder $dailyVisitQuery): Builder {
        return DB::connection()
            ->query()
            ->selectRaw("`date_list`.`date`")
            ->selectRaw("coalesce(`visit`.`visits_count`, 0) as `visits_count`")
            ->fromSub($dateQuery, 'date_list')
            ->leftJoinSub($dailyVisitQuery, 'visit', "date_list.date", "=", "visit.visits_date")
            ->orderByDesc("date");
    }

    /**
     * @param array<int,mixed> $values
     */
    private function getSvgChart(array $values): ?string {
        if ( ! $this->configuration->generateGraphs) {
            return null;
        }

        $chart = LineChart::new(...$values)
            ->withColorGradient(...$this->graphProperties->colors)
            ->withDimensions($this->graphProperties->width_svg, $this->graphProperties->height_svg)
            ->withLockYAxisRange($this->graphProperties->maximum_value_lock)
            ->withMaxItemAmount($this->graphProperties->maximum_days)
            ->withStrokeWidth($this->graphProperties->stroke_width);

        return null === $this->graphProperties->order_reverse || false === $this->graphProperties->order_reverse
            ? $chart->make()
            : $chart->withOrderReversed()->make();
    }

    public function calculateDayMaximumCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->max('visits_count'));
    }

    public function calculateTotalCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->sum('visits_count'));
    }

    public function calculateLast1dayCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->slice(1, 1)->value('visits_count'));
    }

    public function calculateLast7daysCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->take(7)->sum('visits_count'));
    }

    public function calculateLast30daysCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->take(30)->sum('visits_count'));
    }

    public function calculateLast365daysCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->take(365)->sum('visits_count'));
    }
}
