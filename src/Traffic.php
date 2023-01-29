<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Builder\TrafficListQueryBuilder;
use OndrejVrto\Visitors\Builder\TrafficSummaryQueryBuilder;
use OndrejVrto\Visitors\Builder\TrafficSingleModelQueryBuilder;

final class Traffic {
    /** @param Visitable|class-string|Visitable[]|array<class-string>|null $models */
    public function list(Visitable|string|array|null $models = null): TrafficListQueryBuilder {
        return (new TrafficListQueryBuilder())
            ->addModels($models);
    }

    public function single(Visitable&Model $model): TrafficSingleModelQueryBuilder {
        return (new TrafficSingleModelQueryBuilder())
            ->forModel($model);
    }

    public function summary(): TrafficSummaryQueryBuilder {
        return (new TrafficSummaryQueryBuilder());
    }
}
