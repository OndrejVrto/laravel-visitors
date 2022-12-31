<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use OndrejVrto\Visitors\DTO\StatisticsConfigData;
use OndrejVrto\Visitors\Models\VisitorsStatistics;
use OndrejVrto\Visitors\Traits\CalculateStatistics;
use OndrejVrto\Visitors\DTO\ListPossibleQueriesData;
use OndrejVrto\Visitors\Services\StatisticsQueriesBuilder;

class GenerateTotalGraphJob implements ShouldQueue {
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use CalculateStatistics;

    public function __construct(
        private StatisticsConfigData $configuration,
        private ListPossibleQueriesData $listPossibleQueries,
    ) {
    }

    public function handle(): void {
        $queryBuilder = new StatisticsQueriesBuilder($this->configuration);
        $dateQuery = $queryBuilder->dateRangeQuery();
        $totalDailyVisitQuery = $queryBuilder->visitQuery($this->listPossibleQueries);
        $totalDailyNumbersQuery = $queryBuilder->dailyNumbersQuery($dateQuery, $totalDailyVisitQuery);
        $totalDailyNumbers = $totalDailyNumbersQuery->get();

        VisitorsStatistics::create([
            "daily_numbers"           => $totalDailyNumbers,
            "day_maximum"             => $this->calculateDayMaximumCount($totalDailyNumbers),
            "visit_total"             => $this->calculateTotalCount($totalDailyNumbers),
            "visit_last_1_day"        => $this->calculateLast1dayCount($totalDailyNumbers),
            "visit_last_7_days"       => $this->calculateLast7daysCount($totalDailyNumbers),
            "visit_last_30_days"      => $this->calculateLast30daysCount($totalDailyNumbers),
            "visit_last_365_days"     => $this->calculateLast365daysCount($totalDailyNumbers),
            'sumar_countries'         => $queryBuilder->sumarQuery('country')->get(),
            'sumar_languages'         => $queryBuilder->sumarQuery('language')->get(),
            'sumar_operating_systems' => $queryBuilder->sumarQuery('operating_system')->get(),
            'from'                    => $this->configuration->from,
            'to'                      => $this->configuration->to,
            'last_data_id'            => $this->configuration->lastId,
            // 'updated_at'              => now(),
        ]);
    }
}
