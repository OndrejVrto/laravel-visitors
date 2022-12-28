<?php

namespace OndrejVrto\Visitors\Models;

use OndrejVrto\Visitors\Enums\VisitorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use OndrejVrto\Visitors\Database\Factories\VisitorsDailyGraphFactory;

class VisitorsDailyGraph extends BaseVisitors {
    public function __construct(array $attributes = []) {
        $this->configTableName = "daily_graph";

        $this->mergeCasts([
            'category'            => VisitorCategory::class,
            'is_crawler'          => 'boolean',

            'daily_numbers'       => AsCollection::class,
            'day_maximum'         => 'integer',

            'visit_total'         => 'integer',
            'visit_yesterday'     => 'integer',
            'visit_last_7_days'   => 'integer',
            'visit_last_30_days'  => 'integer',
            'visit_last_365_days' => 'integer',
        ]);

        parent::__construct($attributes);
    }

    protected static function newFactory(): Factory {
        return new VisitorsDailyGraphFactory();
    }
}
