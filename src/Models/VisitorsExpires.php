<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\Factory;
use OndrejVrto\Visitors\Contracts\CategoryContract;
use OndrejVrto\Visitors\Database\Factories\VisitorsExpiresFactory;

class VisitorsExpires extends Model {
    public $timestamps = false;

    public $guarded = [];

    public function __construct(array $attributes = []) {
        $this->mergeCasts([
            'id'            => 'integer',
            "viewable_type" => 'string',
            "viewable_id"   => 'integer',
            'ip_address'    => 'string',
            'category'      => CategoryContract::class,
            'expires_at'    => 'datetime',
        ]);

        parent::__construct($attributes);
    }

    public function getConnectionName(): ?string {
        return config('visitors.models.eloquent_connection', parent::getConnectionName());
    }

    public function getTable(): string {
        return config('visitors.models.table_names.expires', parent::getTable());
    }

    protected static function newFactory(): Factory {
        return new VisitorsExpiresFactory();
    }
}
