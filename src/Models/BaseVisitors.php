<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

abstract class BaseVisitors extends Model {
    public $timestamps = false;

    public $guarded = [];

    protected string $configTableName;

    protected $casts = [
        'id'            => 'integer',
        "viewable_type" => 'string',
        "viewable_id"   => 'integer',
    ];

    public function viewable(): MorphTo {
        return $this->morphTo();
    }

    public function getConnectionName(): ?string {
        $nameConnection = config('visitors.models.eloquent_connection');
        return is_string($nameConnection)
            ? $nameConnection
            : parent::getConnectionName();
    }

    public function getTable(): string {
        $nameTable = config("visitors.models.table_names.$this->configTableName");
        return is_string($nameTable)
            ? $nameTable
            : parent::getTable();
    }
}
