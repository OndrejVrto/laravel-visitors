<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use OndrejVrto\Visitors\Database\Factories\VisitorsExpiresFactory;

class VisitorsExpires extends BaseVisitors {
    use MassPrunable;

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
        return static::where('expires_at', '<', now());
    }

    public function scopeWhereIpAddress(Builder $query, ?string $ipAddress = null): Builder {
        return $query
            ->when(
                $ipAddress === null,
                fn ($q) => $q->whereNull('ip_address'),
                fn ($q) => $q->where('ip_address', $ipAddress),
            );
    }
}
