<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

// use Illuminate\Database\Eloquent\Builder;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Models\VisitorsExpires;
use OndrejVrto\Visitors\Models\VisitorsStatistics;
use OndrejVrto\Visitors\Observers\VisitableObserver;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait InteractsWithVisits {
    protected $removeViewsOnDelete = true;

    public static function bootInteractsWithVisits(): void {
        static::observe(VisitableObserver::class);
    }

    public function visitExpires(): MorphMany {
        return $this->morphMany(VisitorsExpires::class, 'viewable');
    }

    public function visitData(): MorphMany {
        return $this->morphMany(VisitorsData::class, 'viewable');
    }

    public function visitStatistics(): MorphMany {
        return $this->morphMany(VisitorsStatistics::class, 'viewable');
    }
}
