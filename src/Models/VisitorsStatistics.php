<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use OndrejVrto\Visitors\Database\Factories\VisitorsStatisticsFactory;

class VisitorsStatistics extends Model {
    use ModelSettings;

    public $timestamps = false;

    public $guarded = [];

    protected string $configTableName = "statistics";

    protected $casts = [
        'id'                      => 'integer',
        'daily_numbers'           => AsCollection::class,
        'day_maximum'             => 'integer',

        'visit_total'             => 'integer',
        'visit_yesterday'         => 'integer',
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

    protected static function newFactory(): Factory {
        return new VisitorsStatisticsFactory();
    }
}
