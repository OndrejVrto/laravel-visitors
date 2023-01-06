<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Builder\TrafficListQueryBuilder;
use OndrejVrto\Visitors\Builder\TrafficOneModelQueryBuilder;

/**
 * @method static TrafficOneModelQueryBuilder forModel(Visitable&Model $visitable)
 * @method static TrafficListQueryBuilder forSeveralModels((Visitable | array | string) $visitable)
 *
 * @see \OndrejVrto\Visitors\Traffic
 */
class Traffic extends Facade {
    protected static function getFacadeAccessor(): string {
        return 'traffic';
    }
}
