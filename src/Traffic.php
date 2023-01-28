<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Action\CheckVisitable;
use OndrejVrto\Visitors\Builder\TrafficListQueryBuilder;
use OndrejVrto\Visitors\Exceptions\InvalidClassParameter;
use OndrejVrto\Visitors\Builder\TrafficSummaryQueryBuilder;
use OndrejVrto\Visitors\Builder\TrafficSingleModelQueryBuilder;

final class Traffic {
    /**
     * @param Visitable|class-string|Visitable[]|array<class-string> $visitable
     * @throws InvalidClassParameter
     */
    public static function forListOfModels(Visitable|string|array $visitable): TrafficListQueryBuilder {
        $visitableClasses = (new CheckVisitable())($visitable);

        if ([] === $visitableClasses) {
            throw new InvalidClassParameter('Used class must by Model and implement Visitable contract.');
        }

        return new TrafficListQueryBuilder($visitableClasses);
    }

    public static function forSingleModel(Visitable&Model $visitable): TrafficSingleModelQueryBuilder {
        return new TrafficSingleModelQueryBuilder($visitable);
    }

    public static function summary(): TrafficSummaryQueryBuilder {
        return new TrafficSummaryQueryBuilder();
    }
}
