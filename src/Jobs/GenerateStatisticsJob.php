<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use OndrejVrto\Visitors\Data\StatisticsConfigData;
use OndrejVrto\Visitors\Models\VisitorsStatistics;
use OndrejVrto\Visitors\Traits\CalculateStatistics;
use OndrejVrto\Visitors\Data\ListPossibleQueriesData;
use OndrejVrto\Visitors\Builder\StatisticsQueriesBuilder;

class GenerateStatisticsJob implements ShouldQueue {
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
                "category"            => $queryData->category,
                "is_crawler"          => $queryData->is_crawler,
                "daily_numbers"       => $dailyNumbers,
                "day_maximum"         => $this->calculateDayMaximumCount($dailyNumbers),
                "visit_total"         => $this->calculateTotalCount($dailyNumbers),
                "visit_last_1_day"    => $this->calculateLast1dayCount($dailyNumbers),
                "visit_last_7_days"   => $this->calculateLast7daysCount($dailyNumbers),
                "visit_last_30_days"  => $this->calculateLast30daysCount($dailyNumbers),
                "visit_last_365_days" => $this->calculateLast365daysCount($dailyNumbers),
                'sumar_countries'         => $queryBuilder->sumarQuery('country', $queryData)->get(),
                'sumar_languages'         => $queryBuilder->sumarQuery('language', $queryData)->get(),
                'sumar_operating_systems' => $queryBuilder->sumarQuery('operating_system', $queryData)->get(),
                'from'                    => $this->configuration->from,
                'to'                      => $this->configuration->to,
                'last_data_id'            => $this->configuration->lastId,
                // 'updated_at'              => now(),
            ];
        }

        VisitorsStatistics::query()
            ->insert($data);
    }
}
