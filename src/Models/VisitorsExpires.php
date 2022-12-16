<?php

namespace OndrejVrto\Visitors\Models;

use OndrejVrto\Visitors\Enums\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use OndrejVrto\Visitors\Database\Factories\VisitorsExpiresFactory;

class VisitorsExpires extends BaseVisitors {
    public function __construct(array $attributes = []) {
        $this->mergeCasts([
            'ip_address'    => 'string',
            'category'      => Category::class,
            'expires_at'    => 'datetime',
        ]);

        $this->configTableName = "expires";

        parent::__construct($attributes);
    }

    protected static function newFactory(): Factory {
        return new VisitorsExpiresFactory();
    }
}
