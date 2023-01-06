<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use OndrejVrto\Visitors\Traits\VisitorsSettings;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OndrejVrto\Visitors\Database\Factories\VisitorsDataFactory;

class VisitorsData extends BaseVisitors {
    use HasFactory;
    use MassPrunable;
    use VisitorsSettings;

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
}
