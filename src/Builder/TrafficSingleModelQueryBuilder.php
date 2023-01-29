<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Builder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use OndrejVrto\Visitors\Models\VisitorsTraffic;
use OndrejVrto\Visitors\Traits\TrafficQueryMethods;

class TrafficSingleModelQueryBuilder {
    use TrafficQueryMethods;

    private function query(): Builder {
        $this->handleConfigurations();

        return VisitorsTraffic::query()
            ->where('category', $this->category)
            ->where('is_crawler', $this->isCrawler)
            ->when(true === $this->withRelationship, fn (Builder $q) => $q->with('viewable'))
            ->whereMorphedTo('viewable', $this->model);
    }

    /**
     * Execute the query and get the first result or null.
     *
     * @param  string[]|string  $columns
     */
    public function first(array|string $columns = ['*']): ?Model {
        return $this->query()->first($columns);
    }
}
