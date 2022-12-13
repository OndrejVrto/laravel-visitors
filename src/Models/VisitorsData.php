<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\Factory;
use OndrejVrto\Visitors\Enums\OperatingSystemsEnum;
use OndrejVrto\Visitors\Database\Factories\VisitorsDataFactory;

class VisitorsData extends Model
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
            'is_crawler'               => 'boolean',
            'country'                  => 'string',
            'language'                 => 'string',
            'operating_system'         => OperatingSystemsEnum::class,
            'visited_at'               => 'datetime',
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
            'is_crawler',
            'country',
            'language',
            'operating_system',
            'visited_at',
        ];
    }

    public function getConnectionName(): ?string {
        return config('visitors.models.connection', parent::getConnectionName());
    }

    public function getTable(): string {
        return config('visitors.models.table_names.data', parent::getTable());
    }

    protected static function newFactory(): Factory {
        return new VisitorsDataFactory();
    }
}
