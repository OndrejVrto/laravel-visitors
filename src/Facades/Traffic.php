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
 * @method static TrafficListQueryBuilder list(Visitable|array|string $models)
 * @method static TrafficSingleModelQueryBuilder single(Visitable&Model $model)
 * @method static TrafficSummaryQueryBuilder summary()
 *
 * @see \OndrejVrto\Visitors\Traffic
 */
class Traffic extends Facade {
    protected static function getFacadeAccessor(): string {
        return 'traffic';
    }
}
