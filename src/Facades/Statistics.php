<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Enums\VisitorCategory;

/**
 * @method static self forModel(Visitable $visitable)
 * @method static self inCategory(VisitorCategory $category)
 * @method static self visitedByCrawlers()
 * @method static self visitedByCrawlersAndPersons()
 * @method static self visitedByPersons()
 * @method static Model sumar((array | string) $columns = ['*'])
 *
 * @see \OndrejVrto\Visitors\Statistics
 */
class Statistics extends Facade {
    protected static function getFacadeAccessor(): string {
        return 'statistics';
    }
}
