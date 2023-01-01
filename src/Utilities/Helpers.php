<?php

declare(strict_types=1);

use OndrejVrto\Visitors\Visitor;
use OndrejVrto\Visitors\Contracts\Visitable;

if (! function_exists('vrtoVisits')) {
    /**
     * TODO: description
     *
     */
    function vrtoVisit(Visitable $model): Visitor {
        return new Visitor($model);
    }
}

if (! function_exists('intOrZero')) {
    /**
     * Return integer ar zero value from mixed value
     *
     */
    function intOrZero(mixed $value): int {
        return is_int($value)
            ? $value
            : 0;
    }
}

if (! function_exists('combinations')) {
    /**
     * Create all possible combinations of values from input arrays
     *
     * @param array<int|string,array<int|string,mixed>> $arrays
     * @return array<int,array<int,mixed>>
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
