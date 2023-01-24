<?php

declare(strict_types=1);

use OndrejVrto\Visitors\Visit;
use OndrejVrto\Visitors\Traffic;
use OndrejVrto\Visitors\Statistics;

if ( ! function_exists('visit')) {
    /**
     * Construct a new Visitor instance.
     */
    function visit(): Visit {
        return new Visit();
    }
}

if ( ! function_exists('traffic')) {
    /**
     * Construct a new Visitor Traffic instance.
     *
     * @return Traffic
     */
    function traffic(): Traffic {
        return new Traffic();
    }
}

if ( ! function_exists('statistics')) {
    /**
     * Construct a new Visitor Statistisc instance.
     *
     * @return Statistics
     */
    function statistics(): Statistics {
        return new Statistics();
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
