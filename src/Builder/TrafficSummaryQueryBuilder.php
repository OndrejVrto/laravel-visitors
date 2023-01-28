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

class TrafficSummaryQueryBuilder {
    use VisitorsSettings;
    use TrafficQueryMethods{
        withRelationship as private;
    }

    private ?int $category = null;

    private ?bool $isCrawler = false;

    private ?string $modelClass = null;

    public function forModel(Visitable&Model $visitable): self {
        $this->modelClass = $visitable->getMorphClass();

        return $this;
    }

    public function inCategory(VisitorCategory $category): self {
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
            ->whereNull('viewable_id')
            ->where('category', '=', $this->category)
            ->where('is_crawler', '=', $this->isCrawler)
            ->where('viewable_type', '=', $this->modelClass);
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
