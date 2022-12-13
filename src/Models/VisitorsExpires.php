<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\Factory;
use OndrejVrto\Visitors\Database\Factories\VisitorsExpiresFactory;

class VisitorsExpires extends Model
{
    public $timestamps = false;

    protected readonly string $morphsName;

    public function __construct(array $attributes = [])
    {
        $this->morphsName = config('visitors.models.model_morph_key', 'model');

        $this->mergeCasts([
            'id'                       => 'integer',
            "{$this->morphsName}_type" => 'string',
            "{$this->morphsName}_id"   => 'integer',
            'tag'                      => 'string',
            'ip_address'               => 'string',
            'expires_at'               => 'datetime',
        ]);

        parent::__construct($attributes);
    }

    /**
     * @return array<string>
     */
    public function getFillable(): array {
        return [
            'id',
            "{$this->morphsName}_type",
            "{$this->morphsName}_id",
            'tag',
            'ip_address',
            'expires_at',
        ];
    }

    public function getConnectionName(): ?string {
        return config('visitors.models.connection', parent::getConnectionName());
    }

    public function getTable(): string {
        return config('visitors.models.table_names.expires', parent::getTable());
    }

    protected static function newFactory(): Factory {
        return new VisitorsExpiresFactory();
    }
}
