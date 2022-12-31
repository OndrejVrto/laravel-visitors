<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use OndrejVrto\Visitors\Database\Factories\VisitorsDataFactory;

class VisitorsData extends BaseVisitors {
    use MassPrunable;

    public function __construct(array $attributes = []) {
        $this->configTableName = "data";

        $this->mergeCasts([
            'category'         => VisitorCategory::class,
            'is_crawler'       => 'boolean',
            'country'          => 'string',
            'language'         => 'string',
            'operating_system' => OperatingSystem::class,
            'visited_at'       => 'datetime',

            // virtual
            'date_from'        => 'datetime',
            'date_to'          => 'datetime',
            'last_id'          => 'integer',
        ]);

        parent::__construct($attributes);
    }

    protected static function newFactory(): Factory {
        return new VisitorsDataFactory();
    }

    public function prunable(): Builder {
        return static::query()
            ->whereDate('visited_at', '<=', now()->subDays($this->numberDaysStatistics()))
            ->limit(5000);
    }

    public function numberDaysStatistics(): int {
        $days = config('visitors.number_days_statistics');
        return is_int($days) && $days >= 1 && $days <= 36500
            ? $days
            : 730;
    }
}
