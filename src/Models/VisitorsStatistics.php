<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Models;

use OndrejVrto\Visitors\Enums\VisitorCategory;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class VisitorsStatistics extends VisitorsBase {
    protected $primaryKey = 'statistics_id';

    protected $casts = [
        "viewable_type"           => 'string',
        'is_crawler'              => 'boolean',
        'category'                => VisitorCategory::class,

        'daily_numbers'           => AsCollection::class,
        'day_maximum'             => 'integer',
        'svg_graph'               => 'string',

        'visit_total'             => 'integer',
        'visit_last_1_day'        => 'integer',
        'visit_last_7_days'       => 'integer',
        'visit_last_30_days'      => 'integer',
        'visit_last_365_days'     => 'integer',

        'sumar_countries'         => AsCollection::class,
        'sumar_languages'         => AsCollection::class,
        'sumar_operating_systems' => AsCollection::class,

        'from'                    => 'datetime',
        'to'                      => 'datetime',
        'last_data_id'            => 'integer',

        'updated_at'              => 'datetime',
    ];

    protected function tableConfigKey(): string {
        return 'statistics';
    }
}
