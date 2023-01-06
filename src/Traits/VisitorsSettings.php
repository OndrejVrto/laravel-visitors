<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

use OndrejVrto\Visitors\Enums\VisitorCategory;

trait VisitorsSettings {
    private function defaultVisitorsEloquentConnection(): ?string {
        $nameConnection = config('visitors.eloquent_connection');
        return is_string($nameConnection)
            ? $nameConnection
            : null;
    }

    private function defaultVisitorsNameTable(string $keyTableName): ?string {
        $nameTable = config("visitors.table_names.$keyTableName");
        return is_string($nameTable)
            ? $nameTable
            : null;
    }

    private function trafficForCrawlersAndPersons(): bool {
        $generate = config('visitors.generate_traffic_for_crawlers_and_persons');
        return is_bool($generate) && $generate;
    }

    private function trafficForCategories(): bool {
        $generate = config('visitors.generate_traffic_for_categories');
        return is_bool($generate) && $generate;
    }

    private function numberDaysStatistics(): int {
        $days = config('visitors.number_days_traffic');
        return is_int($days) && $days >= 1 && $days <= 36500
            ? $days
            : 730;
    }

    private function scheduleGenerateTrafficData(): bool {
        $schedule = config('visitors.schedule_generate_traffic_data_automaticaly');
        return is_bool($schedule)
            ? $schedule
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

    /** @return string[] */
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
