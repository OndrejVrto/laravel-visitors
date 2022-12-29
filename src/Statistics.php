<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use OndrejVrto\Visitors\DTO\ListOptionData;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Services\ListOptions;
use OndrejVrto\Visitors\Traits\StatisticsGetters;
use OndrejVrto\Visitors\Models\VisitorsDailyGraph;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class Statistics {
    use StatisticsGetters;

    private int $numberDaysStatistics;

    private bool $generateCategoryStatistics;

    private bool $generateCrawlersStatistics;

    private Carbon $from;

    private Carbon $to;

    private int $lastId;

    public function __construct() {
        $crawlerStatistics = config('visitors.create_crawlers_statistics');
        $this->generateCrawlersStatistics = is_bool($crawlerStatistics) && $crawlerStatistics;

        $categoryStatistics = config('visitors.create_categories_statistics');
        $this->generateCategoryStatistics = is_bool($categoryStatistics) && $categoryStatistics;

        $this->numberDaysStatistics = self::numberDaysStatistics();

        $range = VisitorsData::query()
            ->selectRaw("MIN(`visited_at`) AS date_from")
            ->selectRaw("MAX(`visited_at`) AS date_to")
            ->selectRaw("MAX(`id`) AS last_id")
            ->first();

        $this->from = $range->date_from;
        $this->to = $range->date_to;
        $this->lastId = $range->last_id;
    }

    public static function numberDaysStatistics(): int {
        $days = config('visitors.number_days_statistics');
        return is_int($days) && $days >= 1 && $days <= 36500
            ? $days
            : 730;
    }


    public function generateStatistics() {
        $listOfOptions = ListOptions::prepare(
            tableName                 : (new VisitorsData())->getTable(),
            generateCategoryStatistics: $this->getGenerateCategoryStatistics(),
            generateCrawlersStatistics: $this->getGenerateCrawlersStatistics(),
            lastId                    : $this->getLastId(),
        );

        dump($listOfOptions);

        $dateQuery = $this->dateListQuery();
        foreach ($listOfOptions as $listOptionData) {

            $dailyVisitQuery = $this->visitQuery($listOptionData);

            $dailyNumbers = $this->dailyNumbersQuery($dateQuery, $dailyVisitQuery)->get();

            $storeData = [
                "viewable_type"       => $listOptionData->viewable_type,
                "viewable_id"         => $listOptionData->viewable_id,
                "category"            => $listOptionData->is_crawler,
                "is_crawler"          => $listOptionData->category,
                "daily_numbers"       => $dailyNumbers->toJson(),
                "day_maximum"         => $this->calculateDayMaximumCount($dailyNumbers),
                "visit_total"         => $this->calculateTotalCount($dailyNumbers),
                "visit_yesterday"     => $this->calculateYesterdayCount($dailyNumbers),
                "visit_last_7_days"   => $this->calculateLast7daysCount($dailyNumbers),
                "visit_last_30_days"  => $this->calculateLast30daysCount($dailyNumbers),
                "visit_last_365_days" => $this->calculateLast365daysCount($dailyNumbers),
            ];

            dd($dailyNumbers, $storeData);

            $status = VisitorsDailyGraph::create($storeData);

            // dump($storeData);
            return $status;
        }
    }

    private function dailyNumbersQuery(Builder $dateQuery, EloquentBuilder $dailyVisitQuery): Builder {
        return DB::table(DB::raw("({$dateQuery->toSql()}) AS DATE_LIST"))
            ->setBindings($dateQuery->getBindings())
            ->selectRaw("DATE_LIST.selected_date")
            ->selectRaw("COALESCE(VISIT.visits_count, 0) AS visits_count")
            ->leftJoin(DB::raw("({$dailyVisitQuery->toSql()}) AS VISIT"), "DATE_LIST.selected_date", "=", "VISIT.visits_date")
            ->addBinding($dailyVisitQuery->getBindings())
            ->orderByDesc("selected_date");
    }

    private function dateListQuery(): Builder {
        $selectedDateQuery = "SELECT ADDDATE('1970-01-01',t4.i*10000 + t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) AS selected_date
            FROM
                (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t0,
                (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t1,
                (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t2,
                (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t3,
                (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t4
            ";
        $selectedDateQuery = preg_replace(['/\r\n|\r|\n/', '/ +/'], " ", $selectedDateQuery);

        return DB::table(DB::raw("({$selectedDateQuery}) AS V"))
            ->whereRaw("selected_date BETWEEN SUBDATE(CURDATE(), INTERVAL ? DAY) AND CURDATE()", [$this->getNumberDaysStatistics()]);
    }

    private function visitQuery(ListOptionData $listOptionData): EloquentBuilder {
        return VisitorsData::query()
            ->selectRaw("DATE(visited_at) AS visits_date")
            ->selectRaw("COUNT(visited_at) AS visits_count")
            ->when(!is_null($listOptionData->viewable_type), fn($q) => $q->where("viewable_type", $listOptionData->viewable_type))
            ->when(!is_null($listOptionData->viewable_id), fn($q) => $q->where("viewable_id", $listOptionData->viewable_id))
            ->when(!is_null($listOptionData->is_crawler), fn($q) => $q->where("is_crawler", $listOptionData->is_crawler))
            ->when(!is_null($listOptionData->category), fn($q) => $q->where("category", $listOptionData->category))
            ->groupBy("visits_date");
    }

    private function calculateDayMaximumCount(Collection $dailyNumbers): int {
        return (int) $dailyNumbers->max('visits_count');
    }

    private function calculateTotalCount(Collection $dailyNumbers): int {
        return (int) $dailyNumbers->sum('visits_count');
    }

    private function calculateYesterdayCount(Collection $dailyNumbers): int {
        return (int) $dailyNumbers->slice(1, 1)->value('visits_count');
    }

    private function calculateLast7daysCount(Collection $dailyNumbers): int {
        return (int) $dailyNumbers->take(7)->sum('visits_count');
    }

    private function calculateLast30daysCount(Collection $dailyNumbers): int {
        return (int) $dailyNumbers->take(30)->sum('visits_count');
    }

    private function calculateLast365daysCount(Collection $dailyNumbers): int {
        return (int) $dailyNumbers->take(365)->sum('visits_count');
    }
}


/**
SELECT DATE_LIST.selected_date, COALESCE(VISIT.visits_count, 0) AS visits_count
FROM
	(SELECT *
	FROM
		(SELECT adddate('1970-01-01',t4.i*10000 + t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) AS selected_date
		FROM
			(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t0,
			(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t1,
			(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t2,
			(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t3,
			(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t4
		) v
	WHERE selected_date BETWEEN SUBDATE(CURDATE(), INTERVAL 365 DAY) AND CURDATE()
	) AS DATE_LIST
LEFT JOIN
	(SELECT
		DATE(visited_at) AS visits_date,
		COUNT(visited_at) AS visits_count
	FROM `visitors_data`
	WHERE
		viewable_type = 'App\\Models\\StaticPage'
		AND
		viewable_id = 73
		AND
		category = 5
		AND
		is_crawler = 0
	GROUP BY visits_date) AS VISIT
ON DATE_LIST.selected_date = VISIT.visits_date
ORDER BY selected_date DESC;
*/
