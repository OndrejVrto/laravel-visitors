<?php

namespace OndrejVrto\Visitors\Models;

use OndrejVrto\Visitors\Enums\Category;
use OndrejVrto\Visitors\Models\BaseVisitors;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use OndrejVrto\Visitors\Database\Factories\VisitorsStatisticsFactory;

class VisitorsStatistics extends BaseVisitors {
    use SoftDeletes;

    public function __construct(array $attributes = []) {
        $this->mergeCasts([
            'category'          => Category::class,
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
}
