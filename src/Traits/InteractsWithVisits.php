<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

use OndrejVrto\Visitors\Enums\Category;
use Illuminate\Database\Eloquent\Builder;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Models\VisitorsExpires;
use OndrejVrto\Visitors\Models\VisitorsStatistics;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait InteractsWithVisits {
    public function visitExpires(): MorphMany {
        return $this->morphMany(VisitorsExpires::class, 'viewable');
    }

    public function visitData(): MorphMany {
        return $this->morphMany(VisitorsData::class, 'viewable');
    }

    public function visitStatistics(): MorphMany {
        return $this->morphMany(VisitorsStatistics::class, 'viewable');
    }

    public function scopeWhereIpAddress(Builder $query, ?string $ipAddress = null): Builder {
        return $query
            ->when(
                $ipAddress === null,
                fn ($q) => $q->whereNull('ip_address'),
                fn ($q) => $q->where('ip_address', $ipAddress),
            );
    }

    public function scopeWhereCategory(Builder $query, ?Category $category = null): Builder {
        return $query
            ->when(
                $category === null,
                fn ($q) => $q->whereNull('category'),
                fn ($q) => $q->where('category', $category),
            );
    }

    // todo this scopes
    public function scopeOrderByVisits(Builder $query): Builder {
        return $query
            ->load('visit_tatistics')
            ->orderByDesc('visit_persons');
    }

    // todo this scopes
    public function scopeOrderByVisitsAcs(Builder $query): Builder {
        return $query
            ->load('visit_tatistics')
            ->orderBy('visit_persons');
    }
}
