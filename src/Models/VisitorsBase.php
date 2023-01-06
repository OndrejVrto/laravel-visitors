<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

abstract class VisitorsBase extends Model {
    public $timestamps = false;

    public $guarded = [];

    abstract protected function getConfigTableName(): string;

    public function getConnectionName(): ?string {
        $nameConnection = config('visitors.eloquent_connection');
        return is_string($nameConnection)
            ? $nameConnection
            : parent::getConnectionName();
    }

    public function getTable(): string {
        $nameTable = config("visitors.table_names.".$this->getConfigTableName());
        return is_string($nameTable)
            ? $nameTable
            : parent::getTable();
    }

    public function viewable(): MorphTo {
        return $this->morphTo('viewable');
    }
}
