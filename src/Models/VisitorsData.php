<?php

namespace OndrejVrto\Visitors\Models;

use OndrejVrto\Visitors\Enums\Category;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use Illuminate\Database\Eloquent\Factories\Factory;
use OndrejVrto\Visitors\Database\Factories\VisitorsDataFactory;

class VisitorsData extends BaseVisitors {
    public function __construct(array $attributes = []) {
        $this->mergeCasts([
            'category'         => Category::class,
            'is_crawler'       => 'boolean',
            'country'          => 'string',
            'language'         => 'string',
            'operating_system' => OperatingSystem::class,
            'visited_at'       => 'datetime',
        ]);

        $this->configTableName = "data";

        parent::__construct($attributes);
    }

    protected static function newFactory(): Factory {
        return new VisitorsDataFactory();
    }
}
