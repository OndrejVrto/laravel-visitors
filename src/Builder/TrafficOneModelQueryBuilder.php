<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Builder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Action\CheckCategory;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use OndrejVrto\Visitors\Models\VisitorsTraffic;
use OndrejVrto\Visitors\Traits\TrafficSettings;

class TrafficOneModelQueryBuilder {
    use TrafficSettings;
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

        if (!$this->trafficForCrawlersAndPersons()) {
            $this->isCrawler = false;
        }
    }

    private function queryOneModel(): Builder {
        $this->handleConfigurations();

        return (new VisitorsTraffic())->query()
            ->whereMorphedTo('viewable', $this->model)
            ->when($this->withRelationship == true, fn (Builder $q) => $q->with('viewable'))
            ->when(is_null($this->isCrawler), fn (Builder $q) => $q->whereNull('is_crawler'))
            ->when(is_bool($this->isCrawler), fn (Builder $q) => $q->where('is_crawler', '=', $this->isCrawler))
            ->when(
                is_null($this->category),
                fn (Builder $q) => $q->whereNull('category'),
                fn (Builder $q) => $q->where('category', '=', $this->category)
            );
    }

    /**
    * Execute the query and get the first result or throw an exception.
    *
    * @param  string[]|string  $columns
    * @return Model|null
    */
    public function get(array|string $columns = ['*']): ?Model {
        return $this->queryOneModel()->first($columns);
    }
}
