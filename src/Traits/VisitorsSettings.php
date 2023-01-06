<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

use OndrejVrto\Visitors\Enums\VisitorCategory;

trait VisitorsSettings {
    private function trafficForCrawlersAndPersons(): bool {
        $conf = config('visitors.generate_traffic_for_crawlers_and_persons');
        return is_bool($conf) && $conf;
    }

    private function trafficForCategories(): bool {
        $conf = config('visitors.generate_traffic_for_categories');
        return is_bool($conf) && $conf;
    }

    private function numberDaysStatistics(): int {
        $days = config('visitors.number_days_traffic');
        return is_int($days) && $days >= 1 && $days <= 36500
            ? $days
            : 730;
    }

    private function scheduleGenerateTrafficData(): bool {
        $scheduleGenerator = config('visitors.schedule_generate_traffic_data_automaticaly');
        return is_bool($scheduleGenerator)
            ? $scheduleGenerator
            : false;
    }

    private function defaultVisitorsCategory(): VisitorCategory {
        $defaultCategory = config('visitors.default_category');
        return is_string($defaultCategory) && VisitorCategory::isValidCase($defaultCategory)
            ? VisitorCategory::fromName($defaultCategory)
            : VisitorCategory::UNDEFINED;
    }

    private function defaultVisitorsExpirationTime(): int {
        $expireTime = config('visitors.expires_time_for_visit');
        return is_int($expireTime)
            ? $expireTime
            : 15;
    }

    private function defaultStorageCrawlersRequests(): bool {
        $crawlerStorage = config('visitors.storage_request_from_crawlers_and_bots');
        return is_bool($crawlerStorage) && $crawlerStorage;
    }

    /**
     * @return string[]
     */
    private function defaultVisitorsIgnoreIPList(): array {
        $defaultIgnoreIP = config('visitors.ignored_ip_addresses');

        if (is_array($defaultIgnoreIP)) {
            return $defaultIgnoreIP;
        }

        if (is_string($defaultIgnoreIP)) {
            return [$defaultIgnoreIP];
        }

        return [];
    }
}
