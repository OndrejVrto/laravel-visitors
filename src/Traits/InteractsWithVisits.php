<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use OndrejVrto\Visitors\Models\VisitorsExpires;
use OndrejVrto\Visitors\Models\VisitorsTraffic;
use OndrejVrto\Visitors\Observers\VisitableObserver;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait InteractsWithVisits {
    use TrafficSettings;

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

    public function visitTraffic(): MorphMany {
        return $this->morphMany(VisitorsTraffic::class, 'viewable');
    }

    public function scopeWithTraffic(
        Builder $query,
        ?VisitorCategory $category = null,
        ?bool $isCrawler = false,
        bool $orderByTotal = true
    ): Builder {
        $modelTraffic = new VisitorsTraffic();
        $dbConnectionName = $modelTraffic->getConnectionName() ?? 'mysql';
        $trafficTableName = $modelTraffic->getTable();

        $isCrawler = $this->trafficForCrawlersAndPersons() ? $isCrawler : false;
        $category = $this->trafficForCategories() ? $category : null;

        $joinQuery = DB::connection($dbConnectionName)
            ->query()
            ->from($trafficTableName)
            ->where('viewable_type', get_class($this))
            ->when(is_null($isCrawler), fn ($q) => $q->whereNull('is_crawler'))
            ->when(is_bool($isCrawler), fn ($q) => $q->where('is_crawler', '=', $isCrawler))
            ->when(
                is_null($category),
                fn ($q) => $q->whereNull('category'),
                fn ($q) => $q->where('category', '=', $category->value)
            );

        return $query
            ->joinSub(
                query   : $joinQuery,
                as      : 'traffic',
                first   : $this->getTable().'.id',
                operator: '=',
                second  : 'traffic.viewable_id',
                type    : 'left'
            )
            ->when($orderByTotal, fn ($q) => $this->scopeOrderByTotal($q));
    }

    public function scopeOrderByTotal(Builder $query, string $direction = 'desc'): Builder {
        return $query->orderBy('visit_total', $direction);
    }

    public function scopeOrderByLastDay(Builder $query, string $direction = 'desc'): Builder {
        return $query->orderBy('visit_last_1_day', $direction);
    }

    public function scopeOrderByLast7Days(Builder $query, string $direction = 'desc'): Builder {
        return $query->orderBy('visit_last_7_days', $direction);
    }

    public function scopeOrderByLast30Days(Builder $query, string $direction = 'desc'): Builder {
        return $query->orderBy('visit_last_30_days', $direction);
    }

    public function scopeOrderByLast365Days(Builder $query, string $direction = 'desc'): Builder {
        return $query->orderBy('visit_last_365_days', $direction);
    }
}
