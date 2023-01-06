<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class VisitorsStatistics extends Model {
    use ModelSettings;

    public $timestamps = false;

    public $guarded = [];

    protected string $configTableName = "statistics";

    protected $casts = [
        "viewable_type"           => 'string',
        'is_crawler'              => 'boolean',
        'category'                => VisitorCategory::class,

        'daily_numbers'           => AsCollection::class,
        'day_maximum'             => 'integer',

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
}
