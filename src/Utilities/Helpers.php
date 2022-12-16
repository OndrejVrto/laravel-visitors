<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Utilities;

use OndrejVrto\Visitors\Visitor;
use OndrejVrto\Visitors\Contracts\Visitable;

if (! function_exists('vrtoVisits')) {
    function vrtoVisit(Visitable $model): Visitor {
        return new Visitor($model);
    }
}
