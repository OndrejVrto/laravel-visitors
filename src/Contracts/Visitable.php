<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Visitable {
    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass();

    public function visitExpires(): MorphMany;

    public function visitData(): MorphMany;

    public function visitTraffic(): MorphMany;
}
