<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

use Carbon\Carbon;

trait StatisticsGetters {
    public function getLastId(): int {
        return $this->lastId;
    }

    public function getFrom(): Carbon {
        return $this->from;
    }

    public function getTo(): Carbon {
        return $this->to;
    }

    public function getGenerateCategoryStatistics(): bool {
        return $this->generateCategoryStatistics;
    }

    public function getGenerateCrawlersStatistics(): bool {
        return $this->generateCrawlersStatistics;
    }

    public function getNumberDaysStatistics(): int {
        return $this->numberDaysStatistics;
    }
}
