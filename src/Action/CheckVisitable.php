<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Action;

use Illuminate\Container\Container;
use OndrejVrto\Visitors\Contracts\Visitable;

class CheckVisitable {
    /**
     * @param Visitable|string|class-string|array<class-string>|Visitable[]|string[] $visitable
     * @return string[]
     */
    public function __invoke(Visitable|string|array $visitable): array {
        $listClasses = [];

        if (is_array($visitable)) {
            foreach ($visitable as $item) {
                $listClasses = [...$listClasses, ...$this->__invoke($item)];
            }
        }

        if (is_string($visitable)) {
            $visitable = Container::getInstance()->make($visitable);
        }

        if ($visitable instanceof Visitable) {
            return [0 => $visitable->getMorphClass()];
        }

        return array_values(array_unique($listClasses));
    }
}