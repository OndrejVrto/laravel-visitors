<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use OndrejVrto\Visitors\DTO\StatisticsConfigData;
use OndrejVrto\Visitors\DTO\ListPossibleQueriesData;

final class StatisticsQueriesBuilder {
    public function __construct(
        private readonly StatisticsConfigData $configuration
    ) {
    }

    public function sumarQuery(string $columnName): Builder {
        return DB::connection($this->configuration->dbConnectionName)
            ->query()
            ->select($columnName)
            ->selectRaw("count(`$columnName`) as `count_$columnName`")
            ->from($this->configuration->dataTableName)
            ->where('id', "<=", $this->configuration->lastId)
            ->groupBy($columnName)
            ->orderByDesc("count_$columnName");
    }

    public function dateRangeQuery(): Builder {
        $dateRangeQuery = "select adddate('2022-01-01', t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) as `date` from
            (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
            (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
            (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2,
            (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t3";
        // $dateRangeQuery = trim(preg_replace('/\s\s+/', ' ', $dateRangeQuery));

        return DB::connection($this->configuration->dbConnectionName)
            ->query()
            ->fromSub($dateRangeQuery, 'x')
            ->whereRaw("`date` between subdate(curdate(), interval ? day) and curdate()", [$this->configuration->numberDaysStatistics]);
    }

    public function visitQuery(ListPossibleQueriesData $listOptionData): Builder {
        return DB::connection($this->configuration->dbConnectionName)
            ->query()
            ->selectRaw("date(`visited_at`) as `visits_date`")
            ->selectRaw("count(`visited_at`) as `visits_count`")
            ->from($this->configuration->dataTableName)
            ->where('id', "<=", $this->configuration->lastId)
            ->when(!is_null($listOptionData->viewable_type), fn ($q) => $q->where("viewable_type", $listOptionData->viewable_type))
            ->when(!is_null($listOptionData->viewable_id), fn ($q) => $q->where("viewable_id", $listOptionData->viewable_id))
            ->when(!is_null($listOptionData->is_crawler), fn ($q) => $q->where("is_crawler", $listOptionData->is_crawler))
            ->when(!is_null($listOptionData->category), fn ($q) => $q->where("category", $listOptionData->category))
            ->groupBy("visits_date");
    }

    public function dailyNumbersQuery(Builder $dateQuery, Builder $dailyVisitQuery): Builder {
        return DB::connection($this->configuration->dbConnectionName)
            ->query()
            ->selectRaw("`date_list`.`date`")
            ->selectRaw("coalesce(`visit`.`visits_count`, 0) as `visits_count`")
            ->fromSub($dateQuery, 'date_list')
            ->leftJoinSub($dailyVisitQuery, 'visit', "date_list.date", "=", "visit.visits_date")
            ->orderByDesc("date");
    }
}
