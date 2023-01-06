<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OndrejVrto\Visitors\Database\Factories\VisitorsExpiresFactory;

class VisitorsExpires extends BaseVisitors {
    use HasFactory;
    use MassPrunable;

    /**
     * @param array<mixed> $attributes
     */
    public function __construct(array $attributes = []) {
        $this->configTableName = "expires";

        $this->mergeCasts([
            'ip_address'    => 'string',
            'category'      => VisitorCategory::class,
            'expires_at'    => 'datetime',
        ]);

        parent::__construct($attributes);
    }

    protected static function newFactory(): Factory {
        return new VisitorsExpiresFactory();
    }

    public function prunable(): Builder {
        return static::query()
            ->whereTime("expires_at", "<", now());
    }
}
