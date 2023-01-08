<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Action\CheckVisitable;
use OndrejVrto\Visitors\Builder\TrafficListQueryBuilder;
use OndrejVrto\Visitors\Exceptions\InvalidClassParameter;
use OndrejVrto\Visitors\Builder\TrafficOneModelQueryBuilder;

class Traffic {
    /**
     * @param Visitable|class-string|Visitable[]|array<class-string> $visitable
     * @throws InvalidClassParameter
     * @return TrafficListQueryBuilder
     */
    public function forSeveralModels(Visitable|string|array $visitable): TrafficListQueryBuilder {
        $visitableClasses = (new CheckVisitable())($visitable);

        if ($visitableClasses === []) {
            throw new InvalidClassParameter('Used class must by Model and implement Visitable contract.');
        }

        return new TrafficListQueryBuilder($visitableClasses);
    }

    /**
     * @param Visitable&Model $visitable
     * @return TrafficOneModelQueryBuilder
     */
    public function forModel(Visitable&Model $visitable): TrafficOneModelQueryBuilder {
        return new TrafficOneModelQueryBuilder($visitable);
    }
}
