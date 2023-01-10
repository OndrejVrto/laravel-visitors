<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use OndrejVrto\Visitors\Traits\VisitorsSettings;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OndrejVrto\Visitors\Database\Factories\VisitorsDataFactory;

class VisitorsData extends VisitorsBase {
    use HasFactory;
    use MassPrunable;
    use VisitorsSettings;

    protected $primaryKey = 'data_id';

    protected $casts = [
        "viewable_type" => 'string',
        "viewable_id"   => 'integer',

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
    ];

    protected function tableConfigKey(): string {
        return 'data';
    }

    public function prunable(): Builder {
        return static::query()
            ->whereDate('visited_at', '<=', now()->subDays($this->numberDaysStatistics()));
    }

    protected static function newFactory(): Factory {
        return new VisitorsDataFactory();
    }
}
