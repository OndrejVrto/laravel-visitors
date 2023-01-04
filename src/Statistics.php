<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Action\CheckCategory;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use OndrejVrto\Visitors\Traits\TrafficSettings;
use OndrejVrto\Visitors\Models\VisitorsStatistics;

class Statistics {
    use TrafficSettings;

    private ?int $category = null;

    private ?bool $isCrawler = false;

    private ?string $modelClass = null;

    public function forModel(Visitable $visitable): self {
        if ($visitable instanceof Model) {
            $this->modelClass = $visitable->getMorphClass();
        }
        return $this;
    }

    public function inCategory(VisitorCategory $category): self {
        $this->category = (new CheckCategory())($category)[0];
        return $this;
    }

    public function visitedByPersons(): self {
        $this->isCrawler = false;
        return $this;
    }

    public function visitedByCrawlers(): self {
        $this->isCrawler = true;
        return $this;
    }

    public function visitedByCrawlersAndPersons(): self {
        $this->isCrawler = null;
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

        return (new VisitorsStatistics())
            ->query()
            ->when(
                !is_null($this->modelClass),
                fn (Builder $q) => $q->where('viewable_type', '=', $this->modelClass)
            )
            ->when(
                is_null($this->isCrawler),
                fn (Builder $q) => $q->whereNull('is_crawler'),
                fn (Builder $q) => $q->where('is_crawler', '=', $this->isCrawler)
            )
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
    * @return Model
    */
    public function sumar(array|string $columns = ['*']): Model {
        return $this->queryOneModel()->firstOrFail($columns);
    }
}
