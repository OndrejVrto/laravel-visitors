<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

use Illuminate\Support\Facades\DB;
use OndrejVrto\Visitors\Facades\Visit;
use Illuminate\Database\Eloquent\Builder;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use OndrejVrto\Visitors\Models\VisitorsExpires;
use OndrejVrto\Visitors\Models\VisitorsTraffic;
use OndrejVrto\Visitors\Observers\VisitableObserver;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OndrejVrto\Visitors\Exceptions\SameDatabaseServer;

trait InteractsWithVisits {
    use VisitorsSettings;

    protected bool $removeDataOnDelete = true;

    private static bool $isSameDatabase;

    public static function bootInteractsWithVisits(): void {
        static::$isSameDatabase = static::isSameDatabasesServer();
        static::observe(VisitableObserver::class);
    }

    private static function isSameDatabasesServer(): bool {
        $modelTraffic = new VisitorsTraffic();
        $visitorsConnectionConfig = $modelTraffic->resolveConnection($modelTraffic->getConnectionName())->getConfig();
        $modelConnectionConfig = static::resolveConnection()->getConfig();

        return ($visitorsConnectionConfig['driver'] === $modelConnectionConfig['driver'])
            && ($visitorsConnectionConfig['host'] === $modelConnectionConfig['host'])
            && ($visitorsConnectionConfig['port'] === $modelConnectionConfig['port']);
    }

    private function enableScopes(string $scopeName): void {
        throw_unless(
            static::$isSameDatabase,
            SameDatabaseServer::class,
            "Scope '{$scopeName}' is disabled. Databases for this model and visitors models must by in same MySql database server. Use separate Eloquent query."
        );
    }

    public function getTable(): string {
        return static::$isSameDatabase
            ? $this->getConnection()->getDatabaseName().'.'.parent::getTable()
            : parent::getTable();
    }

    public function getDefaultRemoveDataOnDelete(): bool {
        return $this->removeDataOnDelete;
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

    public function incrementVisit(): self {
        Visit::forModel($this)->increment();
        // visit($this)->increment();

        return $this;
    }

    public function scopeWithTraffic(
        Builder $query,
        ?VisitorCategory $category = null,
        ?bool $isCrawler = false
    ): Builder {
        $this->enableScopes('withTraffic');

        $modelTraffic = new VisitorsTraffic();
        $dbConnectionName = $modelTraffic->getConnectionName();
        $trafficTableName = $modelTraffic->getTable();

        $isCrawler = $this->trafficForCrawlersAndPersons() ? $isCrawler : false;
        $category = $this->trafficForCategories() ? $category : null;

        $joinQuery = DB::connection($dbConnectionName)
            ->query()
            ->from($trafficTableName)
            ->where('viewable_type', $this::class)
            ->where('is_crawler', '=', $isCrawler)
            ->where('category', '=', $category?->value);

        return $query
            ->joinSub(
                query   : $joinQuery,
                as      : 'traffic',
                first   : $this->getTable().'.id',
                operator: '=',
                second  : 'traffic.viewable_id',
                type    : 'left'
            )
            ->withCasts($modelTraffic->getCasts());
    }

    public function scopeOrderByVisitTotal(Builder $query, string $direction = 'desc'): Builder {
        return $query->orderBy('visit_total', $direction);
    }

    public function scopeOrderByVisitLastDay(Builder $query, string $direction = 'desc'): Builder {
        return $query->orderBy('visit_last_1_day', $direction);
    }

    public function scopeOrderByVisitLast7Days(Builder $query, string $direction = 'desc'): Builder {
        return $query->orderBy('visit_last_7_days', $direction);
    }

    public function scopeOrderByVisitLast30Days(Builder $query, string $direction = 'desc'): Builder {
        return $query->orderBy('visit_last_30_days', $direction);
    }

    public function scopeOrderByVisitLast365Days(Builder $query, string $direction = 'desc'): Builder {
        return $query->orderBy('visit_last_365_days', $direction);
    }
}
