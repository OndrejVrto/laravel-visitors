<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

trait VisitorsSettings {
    private function trafficForCrawlersAndPersons(): bool {
        $conf = config('visitors.generate_traffic_for_crawlers_and_persons');
        return is_bool($conf) && $conf;
    }

    private function trafficForCategories(): bool {
        $conf = config('visitors.generate_traffic_for_categories');
        return is_bool($conf) && $conf;
    }

    public function numberDaysStatistics(): int {
        $days = config('visitors.number_days_traffic');
        return is_int($days) && $days >= 1 && $days <= 36500
            ? $days
            : 730;
    }
}
