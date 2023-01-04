<?php

declare(strict_types=1);

use OndrejVrto\Visitors\Traffic;
use OndrejVrto\Visitors\Visitor;
use OndrejVrto\Visitors\Statistics;
use OndrejVrto\Visitors\Contracts\Visitable;

if (! function_exists('visit')) {
    /**
     * Construct a new Visitor instance.
     */
    function visit(Visitable $model): Visitor {
        return new Visitor($model);
    }
}

if (! function_exists('traffic')) {
    /**
     * Construct a new Visitor Traffic instance.
      *
      * @param Visitable|string|class-string|array<class-string> $visitable
      * @return Traffic
      */
    function traffic(Visitable|string|array $visitable): Traffic {
        return new Traffic($visitable);
    }
}

if (! function_exists('statistics')) {
    /**
     * Construct a new Visitor Statistisc instance.
      *
      * @return Statistics
      */
    function statistics(): Statistics {
        return new Statistics();
    }
}

if (! function_exists('intOrZero')) {
    /**
     * Return integer or zero value from mixed value
     */
    function intOrZero(mixed $value): int {
        return is_int($value)
            ? $value
            : (is_string($value) && ctype_digit($value)
                ? (int) $value
                : 0);
    }
}

if (! function_exists('combinations')) {
    /**
     * Create all possible combinations of values from input arrays
     *
     * @param array<int|string,array<int|string,mixed>> $arrays
     * @return array<int,array<int,mixed>>|array<int|string,mixed>
     */
    function combinations(array $arrays, int $level = 0): array {
        if (!isset($arrays[$level])) {
            return [];
        }

        if ($level === count($arrays) - 1) {
            return $arrays[$level];
        }

        // get combinations from subsequent arrays
        $tmp = combinations($arrays, $level + 1);

        // concat each array from tmp with each element from $arrays[$level]
        $result = [];
        foreach ($arrays[$level] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t)
                    ? [...[$v], ...$t]
                    : [$v, $t];
            }
        }

        return $result;
    }
}
