<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Data;

use Carbon\Carbon;

class StatisticsConfigData {
    public function __construct(
        public readonly int    $lastId,
        public readonly int    $numberDaysStatistics,
        public readonly bool   $generateCategoryStatistics,
        public readonly bool   $generateCrawlersStatistics,
        public readonly Carbon $from,
        public readonly Carbon $to,
        public readonly string $dataTableName,
        public readonly string $graphTableName,
        public readonly string $statisticsTableName,
        public readonly string $dbConnectionName,
    ) {
    }
}
