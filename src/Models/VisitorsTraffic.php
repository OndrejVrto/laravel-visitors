<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Models;

use OndrejVrto\Visitors\Enums\VisitorCategory;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class VisitorsTraffic extends VisitorsBase {
    protected $casts = [
        "viewable_type" => 'string',
        "viewable_id"   => 'integer',

        'category'            => VisitorCategory::class,
        'is_crawler'          => 'boolean',

        'daily_numbers'       => AsCollection::class,
        'day_maximum'         => 'integer',

        'visit_total'         => 'integer',
        'visit_last_1_day'    => 'integer',
        'visit_last_7_days'   => 'integer',
        'visit_last_30_days'  => 'integer',
        'visit_last_365_days' => 'integer',
    ];

    protected function tableConfigKey(): string {
        return 'traffic';
    }
}
