<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Builder;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Action\CheckCategory;
use OndrejVrto\Visitors\Action\CheckVisitable;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use OndrejVrto\Visitors\Traits\TrafficSettings;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use OndrejVrto\Visitors\Exceptions\InvalidClassParameter;

class VisitorsTraffic extends BaseVisitors {
    use TrafficSettings;

    public function __construct(array $attributes = []) {
        $this->configTableName = "traffic";

        $this->mergeCasts([
            'category'            => VisitorCategory::class,
            'is_crawler'          => 'boolean',

            'daily_numbers'       => AsCollection::class,
            'day_maximum'         => 'integer',

            'visit_total'         => 'integer',
            'visit_last_1_day'    => 'integer',
            'visit_last_7_days'   => 'integer',
            'visit_last_30_days'  => 'integer',
            'visit_last_365_days' => 'integer',
        ]);

        parent::__construct($attributes);
    }

    /**
     * @param Visitable|string|class-string|array<class-string> $visitable
     * @throws InvalidClassParameter
     */
    public function trafficList(Visitable|string|array $visitable): Builder {
        $classes = (new CheckVisitable())($visitable);
        $countClasses = count($classes);

        if ($countClasses === 0) {
            throw new InvalidClassParameter('Empty or bad parameter $visitable. Used class must implement Visitable contract.');
        }

        return self::query()
            ->when(
                $countClasses === 1,
                fn (Builder $q) => $q->where('viewable_type', $classes[0]),
                fn (Builder $q) => $q->whereIn('viewable_type', $classes)
            )
            ->whereNotNull('viewable_id')
            ->when($this->trafficForCategories() === false, fn (Builder $q) => $q->whereNull('category'))
            ->when($this->trafficForCrawlersAndPersons() === false, fn (Builder $q) => $q->where('is_crawler', false));
    }

    /**
     * @param VisitorCategory|string|int|VisitorCategory[]|string[]|int[] $category
     */
    public function scopeInCategory(Builder $query, VisitorCategory|string|int|array $category): Builder {
        $categories = (new CheckCategory())($category);
        $countCategories = count($categories);

        return $query
            ->when(
                $this->trafficForCategories() === true,
                fn (Builder $query) => $query
                    ->when($countCategories === 1, fn (Builder $q) => $q->where('category', $categories[0]))
                    ->when($countCategories > 1, fn (Builder $q) => $q->whereIn('category', $categories))
            );
    }

    public function scopeVisitedByPersons(Builder $query): Builder {
        return $query
            ->when($this->trafficForCrawlersAndPersons() === true, fn (Builder $q) => $q->where('is_crawler', '=', false));
    }

    public function scopeVisitedByCrawlers(Builder $query): Builder {
        return $query
            ->when($this->trafficForCrawlersAndPersons() === true, fn (Builder $q) => $q->where('is_crawler', '=', true));
    }

    public function scopeWithRelationships(Builder $query): Builder {
        return $query->with('viewable');
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
