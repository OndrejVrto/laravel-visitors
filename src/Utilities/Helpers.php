<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Utilities;

use OndrejVrto\Visitors\Visitor;
use Illuminate\Database\Eloquent\Model;

if (! function_exists('vrtoVisits'))
{
    function vrtoVisit(Model $subject): Visitor {
        return new Visitor($subject);
    }
}
