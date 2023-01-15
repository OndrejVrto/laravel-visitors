<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Utilities;

use Illuminate\Support\Arr;

class CartesianCombinations {
    /** @var array<mixed> */
    private array $inputItems = [];

    /**
     * @param array<mixed> $item
     * @return self
     */
    public function forItem(?array $item): self {
        if (null !== $item) {
            $this->inputItems = $item;
        }

        return $this;
    }

    /**
     * @param boolean $when
     * @param array<mixed> $trueItem
     * @param array<mixed> $falseItem
     * @return self
     */
    public function addItemWhen(bool $when, array $trueItem, ?array $falseItem = null): self {
        if ($when) {
            $this->inputItems[] = $trueItem;
        } elseif (null !== $falseItem) {
            $this->inputItems[] = $falseItem;
        }

        return $this;
    }

    /**
     * Create all possible combinations of values from input arrays
     *
     * @return array<int,mixed>
     */
    public function get(): array {
        $prepareInput = $this->prepareInputItems();

        return $this->combinations($prepareInput);
    }

    /**
     * @return array<int,array<int,mixed>>
     */
    private function prepareInputItems(): array {
        $prepareTmp = Arr::map($this->inputItems, function ($node): array {
            if (is_array($node)) {
                return Arr::flatten($node);
            }

            if (null === $node) {
                return [];
            }

            return [$node];
        });

        return array_values($prepareTmp);
    }

    /**
     * @param array<int,array<int,mixed>> $arrays
     * @param integer $level
     * @return array<int,mixed>
     */
    private function combinations(array $arrays, int $level = 0): array {
        if ( ! isset($arrays[$level])) {
            return [[]];
        }

        if ($level === count($arrays) - 1) {
            return 0 === $level
                ? [$arrays[$level]]
                : $arrays[$level];
        }

        // get combinations from subsequent arrays
        $tmp = $this->combinations($arrays, $level + 1);

        // concat each array from tmp with each element from $arrays[$level]
        $result = [];
        foreach ($arrays[$level] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t)
                    ? [...[$v], ...$t]
                    : [$v, $t];
            }
        }

        return array_values($result);
    }
}
