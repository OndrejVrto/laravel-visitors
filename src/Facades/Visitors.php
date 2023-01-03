<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Facades;

use OndrejVrto\Visitors\Visitor;
use Illuminate\Support\Facades\Facade;

/**
 * @see \OndrejVrto\Visitors\Visitor
 */
class Visitors extends Facade {
    protected static function getFacadeAccessor() {
        return Visitor::class;
    }
}
