<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OndrejVrto\Visitors\Database\Factories\VisitorsExpiresFactory;

class VisitorsExpires extends VisitorsBase {
    use HasFactory;
    use MassPrunable;

    protected $casts = [
        "viewable_type" => 'string',
        "viewable_id"   => 'integer',

        'ip_address'    => 'string',
        'category'      => VisitorCategory::class,
        'expires_at'    => 'datetime',
    ];

    protected function tableConfigKey(): string {
        return 'expires';
    }

    public function prunable(): Builder {
        return static::query()
        ->whereTime("expires_at", "<", now());
    }

    protected static function newFactory(): Factory {
        return new VisitorsExpiresFactory();
    }
}
