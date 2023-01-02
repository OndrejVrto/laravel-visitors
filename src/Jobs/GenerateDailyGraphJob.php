<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use OndrejVrto\Visitors\Models\VisitorsTraffic;
use OndrejVrto\Visitors\DTO\StatisticsConfigData;
use OndrejVrto\Visitors\Traits\CalculateStatistics;
use OndrejVrto\Visitors\DTO\ListPossibleQueriesData;
use OndrejVrto\Visitors\Services\StatisticsQueriesBuilder;

class GenerateDailyGraphJob implements ShouldQueue {
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use CalculateStatistics;

    /**
     * @param Collection<int,ListPossibleQueriesData> $listPossibleQueries
     */
    public function __construct(
        private readonly StatisticsConfigData $configuration,
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

            $data[] = [
                "viewable_type"       => $queryData->viewable_type,
                "viewable_id"         => $queryData->viewable_id,
                "category"            => $queryData->category,
                "is_crawler"          => $queryData->is_crawler,
                "daily_numbers"       => $dailyNumbers,
                "day_maximum"         => $this->calculateDayMaximumCount($dailyNumbers),
                "visit_total"         => $this->calculateTotalCount($dailyNumbers),
                "visit_last_1_day"    => $this->calculateLast1dayCount($dailyNumbers),
                "visit_last_7_days"   => $this->calculateLast7daysCount($dailyNumbers),
                "visit_last_30_days"  => $this->calculateLast30daysCount($dailyNumbers),
                "visit_last_365_days" => $this->calculateLast365daysCount($dailyNumbers),
                ];
        }

        VisitorsTraffic::query()
            ->insert($data);
    }
}
