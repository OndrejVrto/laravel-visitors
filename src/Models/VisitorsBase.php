<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Traits\VisitorsSettings;
use Illuminate\Database\Eloquent\Relations\MorphTo;

abstract class VisitorsBase extends Model {
    use VisitorsSettings;

    public $timestamps = false;

    public $guarded = [];

    abstract protected function tableConfigKey(): string;

    public function getConnectionName(): ?string {
        return $this->defaultVisitorsEloquentConnection() ?? parent::getConnectionName();
    }

    public function getTable(): string {
        return $this->defaultVisitorsNameTable($this->tableConfigKey()) ?? parent::getTable();
    }

    public function viewable(): MorphTo {
        return $this->morphTo('viewable');
    }
}
