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
            $this->inputItems[] = $item;
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
     * @param array<int,array<int,mixed>> $set
     * @return array<int,mixed>
     */
    private function combinations(array $set): array {
        if ( ! $set) {
            return [[]];
        }

        $subset = array_shift($set);
        $cartesianSubset = $this->combinations($set);

        $result = [];
        foreach ($subset as $value) {
            foreach ($cartesianSubset as $p) {
                array_unshift($p, $value);
                $result[] = $p;
            }
        }

        return $result;
    }
}
