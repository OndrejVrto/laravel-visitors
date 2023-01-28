<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Builder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Action\CheckCategory;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use OndrejVrto\Visitors\Models\VisitorsTraffic;
use OndrejVrto\Visitors\Traits\VisitorsSettings;

class TrafficSingleModelQueryBuilder {
    use VisitorsSettings;
    use TrafficQueryMethods;

    private Visitable&Model $model;

    private ?int $category = null;

    private ?bool $isCrawler = false;

    private ?bool $withRelationship = null;

    /**
     * @param Visitable&Model $visitable
     */
    public function __construct(Visitable&Model $visitable) {
        $this->model = $visitable;
    }

    /**
     * @param VisitorCategory|string|int $category
     */
    public function inCategory(VisitorCategory|string|int $category): self {
        $this->category = (new CheckCategory())($category)[0];
        return $this;
    }

    private function handleConfigurations(): void {
        $this->category = $this->trafficForCategories()
            ? $this->category
            : null;

        if ( ! $this->trafficForCrawlersAndPersons()) {
            $this->isCrawler = false;
        }
    }

    private function query(): Builder {
        $this->handleConfigurations();

        return VisitorsTraffic::query()
            ->whereMorphedTo('viewable', $this->model)
            ->when(true === $this->withRelationship, fn (Builder $q) => $q->with('viewable'))
            ->when(null === $this->isCrawler, fn (Builder $q) => $q->whereNull('is_crawler'))
            ->when(is_bool($this->isCrawler), fn (Builder $q) => $q->where('is_crawler', '=', $this->isCrawler))
            ->when(
                null === $this->category,
                fn (Builder $q) => $q->whereNull('category'),
                fn (Builder $q) => $q->where('category', '=', $this->category)
            );
    }

    /**
     * Execute the query and get the first result or null.
     *
     * @param  string[]|string  $columns
     * @return Model|null
     */
    public function first(array|string $columns = ['*']): ?Model {
        return $this->query()->first($columns);
    }
}
