<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Builder\TrafficListQueryBuilder;
use OndrejVrto\Visitors\Builder\TrafficSummaryQueryBuilder;
use OndrejVrto\Visitors\Builder\TrafficSingleModelQueryBuilder;

/**
 * @method static TrafficListQueryBuilder forListOfModels(Visitable|array|string $visitable)
 * @method static TrafficSingleModelQueryBuilder forSingleModel(Visitable&Model $visitable)
 * @method static TrafficSummaryQueryBuilder summary()
 *
 * @see \OndrejVrto\Visitors\Traffic
 */
class Traffic extends Facade {
    protected static function getFacadeAccessor(): string {
        return 'traffic';
    }
}
