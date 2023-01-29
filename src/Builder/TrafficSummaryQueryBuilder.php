<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Builder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use OndrejVrto\Visitors\Models\VisitorsTraffic;
use OndrejVrto\Visitors\Traits\TrafficQueryMethods;

class TrafficSummaryQueryBuilder {
    use TrafficQueryMethods{
        withRelationship as protected;
    }

    private function query(): Builder {
        $this->handleConfigurations();

        return VisitorsTraffic::query()
            ->where("category", $this->category)
            ->where("is_crawler", $this->isCrawler)
            ->where("viewable_type", $this->model?->getMorphClass())
            ->whereNull('viewable_id');
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
