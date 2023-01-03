<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

trait TrafficSettings {
    public function trafficForCrawlersAndPersons(): bool {
        $conf = config('visitors.generate_traffic_for_crawlers_and_persons');
        return is_bool($conf) && $conf;
    }

    public function trafficForCategories(): bool {
        $conf = config('visitors.generate_traffic_for_categories');
        return is_bool($conf) && $conf;
    }
}
