<?php

declare(strict_types=1);

use OndrejVrto\Visitors\Visit;
use OndrejVrto\Visitors\Traffic;
use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Contracts\Visitable;

if ( ! function_exists('visit')) {
    /**
     * Construct a new Visitor instance.
     */
    function visit(Visitable&Model $model): Visit {
        return (new Visit())->model($model);
    }
}

if ( ! function_exists('traffic')) {
    /**
     * Construct a new Visitor Traffic instance.
     *
     */
    function traffic(): Traffic {
        return new Traffic();
    }
}

if ( ! function_exists('intOrZero')) {
    /**
     * Return integer or zero value from mixed value
     */
    function intOrZero(mixed $value): int {
        return match (true) {
            is_int($value) => $value,
            is_bool($value) => (int) $value,
            is_numeric($value) => (int) $value,
            (is_string($value) && ctype_digit($value)) => (int) $value,
            default => 0,
        };
    }
}
