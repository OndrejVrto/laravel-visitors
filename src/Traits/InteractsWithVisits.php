<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Models\VisitorsExpires;
use OndrejVrto\Visitors\Models\VisitorsDailyGraph;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use OndrejVrto\Visitors\Observers\VisitableObserver;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait InteractsWithVisits {
    protected $removeDataOnDelete = true;

    public static function bootInteractsWithVisits(): void {
        static::observe(VisitableObserver::class);
    }

    public function visitExpires(): MorphMany {
        return $this->morphMany(VisitorsExpires::class, 'viewable');
    }

    public function visitData(): MorphMany {
        return $this->morphMany(VisitorsData::class, 'viewable');
    }

    public function visitDailyGraphs(): MorphMany {
        return $this->morphMany(VisitorsDailyGraph::class, 'viewable');
    }

    public function dailyVisitGraph(): MorphOne {
        return $this->morphOne(VisitorsDailyGraph::class, 'viewable')->whereNull('category')->where('is_crawler', false);
    }
}
