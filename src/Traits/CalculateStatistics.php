<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

use Illuminate\Support\Collection;

trait CalculateStatistics {
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
