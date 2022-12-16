<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use OndrejVrto\Visitors\Database\Factories\VisitorsStatisticsFactory;

class VisitorsStatistics extends BaseVisitors {
    use SoftDeletes;

    public function __construct(array $attributes = []) {
        $this->mergeCasts([
            'category'          => VisitorCategory::class,
            'visit_yesterday'   => 'integer',
            'visit_this_week'   => 'integer',
            'visit_this_month'  => 'integer',
            'visit_last_year'   => 'integer',
            'visit_persons'     => 'integer',
            'visit_crawlers'    => 'integer',
            'visit_total'       => 'integer',
            'daily_numbers'     => AsCollection::class,
            'weekly_numbers'    => AsCollection::class,
            'monthly_numbers'   => AsCollection::class,
            'annual_numbers'    => AsCollection::class,
            'countries'         => AsCollection::class,
            'languages'         => AsCollection::class,
            'operating_systems' => AsCollection::class,
            'updated_at'        => 'datetime',
        ]);

        $this->configTableName = "statistics";

        parent::__construct($attributes);
    }

    protected static function newFactory(): Factory {
        return new VisitorsStatisticsFactory();
    }

    // todo this scopes
    public function scopeOrderByVisits(Builder $query): Builder {
        return $query
            ->orderByDesc('visit_persons');
    }

    // todo this scopes
    public function scopeOrderByVisitsAcs(Builder $query): Builder {
        return $query
            ->orderBy('visit_persons');
    }
}
