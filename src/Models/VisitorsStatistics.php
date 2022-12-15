<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\Factory;
use OndrejVrto\Visitors\Contracts\CategoryContract;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use OndrejVrto\Visitors\Database\Factories\VisitorsStatisticsFactory;

class VisitorsStatistics extends Model {
    use SoftDeletes;

    public $timestamps = false;

    public $guarded = [];

    public function __construct(array $attributes = []) {
        $this->mergeCasts([
            'id'                 => 'integer',
            "viewable_type"      => 'string',
            "viewable_id"        => 'integer',
            'category'           => CategoryContract::class,

            'visit_yesterday'    => 'integer',
            'visit_this_week'    => 'integer',
            'visit_this_month'   => 'integer',
            'visit_last_year'    => 'integer',
            'visit_persons'      => 'integer',
            'visit_crawlers'     => 'integer',
            'visit_total'        => 'integer',

            'daily_numbers'      => AsCollection::class,
            'weekly_numbers'     => AsCollection::class,
            'monthly_numbers'    => AsCollection::class,
            'annual_numbers'     => AsCollection::class,

            'countries'          => AsCollection::class,
            'languages'          => AsCollection::class,
            'operating_systems'  => AsCollection::class,

            'updated_at'         => 'datetime',
        ]);

        parent::__construct($attributes);
    }

    public function getConnectionName(): ?string {
        $nameConnection = config('visitors.models.eloquent_connection');
        return is_string($nameConnection)
            ? $nameConnection
            : parent::getConnectionName();
    }

    public function getTable(): string {
        $nameTable = config('vvisitors.models.table_names.statistics');
        return is_string($nameTable)
            ? $nameTable
            : parent::getTable();
    }

    protected static function newFactory(): Factory {
        return new VisitorsStatisticsFactory();
    }
}
