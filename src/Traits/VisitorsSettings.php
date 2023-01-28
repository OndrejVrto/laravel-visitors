<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

use OndrejVrto\Visitors\Enums\VisitorCategory;

trait VisitorsSettings {
    private function defaultVisitorsNameTable(string $keyTableName): ?string {
        $nameTable = config("visitors.table_names.{$keyTableName}");

        if (is_string($nameTable)) {
            return $nameTable;
        }

        return match ($keyTableName) {
            'info'       => 'visitors_info',
            'data'       => 'visitors_data',
            'expires'    => 'visitors_expires',
            'traffic'    => 'visitors_traffic',
            default      => null,
        };
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

    private function defaultGenerateGraphs(): bool {
        $generateGraphs = config('visitors.generate_graphs');
        return is_bool($generateGraphs) && $generateGraphs;
    }

    private function graphMaximumValue(): ?int {
        $graphMaxValue = config("visitors.custom_graphs_properties.maximum_value_lock");
        return is_int($graphMaxValue)
            ? $graphMaxValue
            : null;
    }

    private function graphMaximumDays(): ?int {
        $graphMaxDays = config("visitors.custom_graphs_properties.maximum_days");
        return is_int($graphMaxDays)
            ? $graphMaxDays
            : null;
    }

    private function graphOrderReversed(): bool {
        $orderReverse = config("visitors.custom_graphs_properties.order_reverse");
        return is_bool($orderReverse) && $orderReverse;
    }

    private function graphWidthSvg(): ?int {
        $graphWidthSvg = config("visitors.custom_graphs_properties.width_svg");
        return is_int($graphWidthSvg)
            ? $graphWidthSvg
            : null;
    }

    private function graphHeighthSvg(): ?int {
        $graphHeighthSvg = config("visitors.custom_graphs_properties.height_svg");
        return is_int($graphHeighthSvg)
            ? $graphHeighthSvg
            : null;
    }

    private function graphStrokeWidth(): ?float {
        $graphStrokeWidth = config("visitors.custom_graphs_properties.stroke_width");
        return is_numeric($graphStrokeWidth)
            ? (float) $graphStrokeWidth
            : null;
    }

    /** @return string[] */
    private function graphColors(): array {
        $graphColors = config("visitors.custom_graphs_properties.colors");
        return is_array($graphColors) && [] !== $graphColors
            ? $graphColors
            : [];
    }
}
