<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Visitable {
    /**  @return string */
    public function getMorphClass();

    public function visitExpires(): MorphMany;

    public function visitData(): MorphMany;

    public function visitTraffic(): MorphMany;
}
