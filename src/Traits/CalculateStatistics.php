<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

use Illuminate\Support\Collection;

trait CalculateStatistics {
    public static function calculateDayMaximumCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->max('visits_count'));
    }

    public static function calculateTotalCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->sum('visits_count'));
    }

    public static function calculateLast1dayCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->slice(1, 1)->value('visits_count'));
    }

    public static function calculateLast7daysCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->take(7)->sum('visits_count'));
    }

    public static function calculateLast30daysCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->take(30)->sum('visits_count'));
    }

    public static function calculateLast365daysCount(Collection $dailyNumbers): int {
        return intOrZero($dailyNumbers->take(365)->sum('visits_count'));
    }
}
