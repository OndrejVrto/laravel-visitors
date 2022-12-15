<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use Illuminate\Database\Eloquent\Factories\Factory;
use OndrejVrto\Visitors\Contracts\CategoryContract;
use OndrejVrto\Visitors\Database\Factories\VisitorsDataFactory;

class VisitorsData extends Model {
    public $timestamps = false;

    public $guarded = [];

    public function __construct(array $attributes = []) {
        $this->mergeCasts([
            'id'               => 'integer',
            "viewable_type"    => 'string',
            "viewable_id"      => 'integer',
            'category'         => CategoryContract::class,
            'is_crawler'       => 'boolean',
            'country'          => 'string',
            'language'         => 'string',
            'operating_system' => OperatingSystem::class,
            'visited_at'       => 'datetime',
        ]);

        parent::__construct($attributes);
    }

    public function getConnectionName(): ?string {
        return config('visitors.models.eloquent_connection', parent::getConnectionName());
    }

    public function getTable(): string {
        return config('visitors.models.table_names.data', parent::getTable());
    }

    protected static function newFactory(): Factory {
        return new VisitorsDataFactory();
    }
}
