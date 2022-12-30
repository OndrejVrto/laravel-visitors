<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use OndrejVrto\Visitors\DTO\ListOptionData;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Services\ListOptions;
use OndrejVrto\Visitors\Models\VisitorsDailyGraph;
use OndrejVrto\Visitors\Models\VisitorsStatistics;

class Statistics {
    private readonly int $numberDaysStatistics;

    private readonly bool $generateCategoryStatistics;

    private readonly bool $generateCrawlersStatistics;

    private readonly Carbon $from;

    private readonly Carbon $to;

    private readonly int $lastId;

    private readonly string $dataTableName;

    private readonly Connection $dbConnection;

    public function __construct() {
        $crawlerStatistics = config('visitors.create_crawlers_statistics');
        $this->generateCrawlersStatistics = is_bool($crawlerStatistics) && $crawlerStatistics;

        $categoryStatistics = config('visitors.create_categories_statistics');
        $this->generateCategoryStatistics = is_bool($categoryStatistics) && $categoryStatistics;

        $this->numberDaysStatistics = static::numberDaysStatistics();

        $range = VisitorsData::query()
            ->selectRaw("min(`visited_at`) as `date_from`")
            ->selectRaw("max(`visited_at`) as `date_to`")
            ->selectRaw("max(`id`) as `last_id`")
            ->first();

        if (!$range instanceof \OndrejVrto\Visitors\Models\VisitorsData) {
            throw new \Exception("Visitor data table don't exists.");
        }

        $from = $range->getAttributeValue('date_from');
        $to = $range->getAttributeValue('date_to');
        $lastId = $range->getAttributeValue('last_id');

        $this->from = ($from instanceof Carbon) ? $from : Carbon::now()->subYear();
        $this->to = ($to instanceof Carbon) ? $to : Carbon::now();
        $this->lastId = is_int($lastId) ? $lastId : 1;

        $visitorData = new VisitorsData();
        $this->dataTableName = $visitorData->getTable();
        $this->dbConnection = DB::connection($visitorData->getConnectionName());
    }

    public static function numberDaysStatistics(): int {
        $days = config('visitors.number_days_statistics');
        return is_int($days) && $days >= 1 && $days <= 36500
            ? $days
            : 730;
    }

    public function generateStatistics(): void {
        $this->dbConnection
            ->table((new VisitorsStatistics())->getTable())
            ->truncate();
        $this->dbConnection
            ->table((new VisitorsDailyGraph())->getTable())
            ->truncate();

        $dateQuery = $this->dateListQuery();

        // Generate Statistics table
        $totalDailyVisitQuery = $this->visitQuery(new ListOptionData(null, null, null, null));
        $totalDailyNumbers = $this->dailyNumbersQuery($dateQuery, $totalDailyVisitQuery)->get();

        VisitorsStatistics::create([
            "daily_numbers"           => $totalDailyNumbers,
            "day_maximum"             => $this->calculateDayMaximumCount($totalDailyNumbers),
            "visit_total"             => $this->calculateTotalCount($totalDailyNumbers),
            "visit_yesterday"         => $this->calculateYesterdayCount($totalDailyNumbers),
            "visit_last_7_days"       => $this->calculateLast7daysCount($totalDailyNumbers),
            "visit_last_30_days"      => $this->calculateLast30daysCount($totalDailyNumbers),
            "visit_last_365_days"     => $this->calculateLast365daysCount($totalDailyNumbers),
            'sumar_countries'         => $this->sumarQuery('country')->get(),
            'sumar_languages'         => $this->sumarQuery('language')->get(),
            'sumar_operating_systems' => $this->sumarQuery('operating_system')->get(),
            'from'                    => $this->from,
            'to'                      => $this->to,
            'last_data_id'            => $this->lastId,
            'updated_at'              => now(),
        ]);

        // generate daily graphs
        ListOptions::prepare(
            dbConnection              : $this->dbConnection,
            tableName                 : $this->dataTableName,
            generateCategoryStatistics: $this->generateCategoryStatistics,
            generateCrawlersStatistics: $this->generateCrawlersStatistics,
            lastId                    : $this->lastId,
        )->each(function($option) use ($dateQuery) {
            $dailyVisitQuery = $this->visitQuery($option);

            // dispatch this start
            $dailyNumbers = $this->dailyNumbersQuery($dateQuery, $dailyVisitQuery)->get();

            VisitorsDailyGraph::create([
                "viewable_type"       => $option->viewable_type,
                "viewable_id"         => $option->viewable_id,
                "category"            => $option->category,
                "is_crawler"          => $option->is_crawler,
                "daily_numbers"       => $dailyNumbers,
                "day_maximum"         => $this->calculateDayMaximumCount($dailyNumbers),
                "visit_total"         => $this->calculateTotalCount($dailyNumbers),
                "visit_yesterday"     => $this->calculateYesterdayCount($dailyNumbers),
                "visit_last_7_days"   => $this->calculateLast7daysCount($dailyNumbers),
                "visit_last_30_days"  => $this->calculateLast30daysCount($dailyNumbers),
                "visit_last_365_days" => $this->calculateLast365daysCount($dailyNumbers),
            ]);
            // dispatch end
        });
    }

    private function sumarQuery(string $columnName): Builder {
        return $this->dbConnection
            ->query()
            ->select($columnName)
            ->selectRaw("count(`$columnName`) as `count_$columnName`")
            ->from($this->dataTableName)
            ->where('id', "<=", $this->lastId)
            ->groupBy($columnName)
            ->orderByDesc("count_$columnName");
    }

    private function dailyNumbersQuery(Builder $dateQuery, Builder $dailyVisitQuery): Builder {
        return $this->dbConnection
            ->query()
            ->selectRaw("`date_list`.`selected_date`")
            ->selectRaw("coalesce(`visit`.`visits_count`, 0) as `visits_count`")
            ->fromSub($dateQuery, 'date_list')
            ->leftJoinSub($dailyVisitQuery, 'visit', "date_list.selected_date", "=", "visit.visits_date")
            ->orderByDesc("selected_date");
    }

    private function dateListQuery(): Builder {
        $dateRangeQuery = "select adddate('2022-01-01', t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) as `selected_date` from
            (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
            (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
            (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2,
            (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t3";
        $dateRangeQuery = trim(preg_replace('/\s\s+/', ' ', $dateRangeQuery));

        return $this->dbConnection
            ->query()
            ->fromSub($dateRangeQuery, 'x')
            ->whereRaw("`selected_date` between subdate(curdate(), interval ? day) and curdate()", [$this->numberDaysStatistics]);
    }

    private function visitQuery(ListOptionData $listOptionData): Builder {
        return $this->dbConnection
            ->query()
            ->selectRaw("date(`visited_at`) as `visits_date`")
            ->selectRaw("count(`visited_at`) as `visits_count`")
            ->from($this->dataTableName)
            ->where('id', "<=", $this->lastId)
            ->when(!is_null($listOptionData->viewable_type), fn ($q) => $q->where("viewable_type", $listOptionData->viewable_type))
            ->when(!is_null($listOptionData->viewable_id), fn ($q) => $q->where("viewable_id", $listOptionData->viewable_id))
            ->when(!is_null($listOptionData->is_crawler), fn ($q) => $q->where("is_crawler", $listOptionData->is_crawler))
            ->when(!is_null($listOptionData->category), fn ($q) => $q->where("category", $listOptionData->category))
            ->groupBy("visits_date");
    }

    private function calculateDayMaximumCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->max('visits_count'));
    }

    private function calculateTotalCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->sum('visits_count'));
    }

    private function calculateYesterdayCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->slice(1, 1)->value('visits_count'));
    }

    private function calculateLast7daysCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->take(7)->sum('visits_count'));
    }

    private function calculateLast30daysCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->take(30)->sum('visits_count'));
    }

    private function calculateLast365daysCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->take(365)->sum('visits_count'));
    }
}
