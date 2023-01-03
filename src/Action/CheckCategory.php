<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Action;

use Illuminate\Support\Str;
use OndrejVrto\Visitors\Enums\VisitorCategory;

class CheckCategory {
    /**
     * @param VisitorCategory|string|int|VisitorCategory[]|string[]|int[] $category
     * @return int[]
     */
    public function __invoke(VisitorCategory|string|int|array $category): array {
        $listCategories = [];

        if (is_array($category)) {
            foreach ($category as $item) {
                $listCategories = [...$listCategories, ...$this->__invoke($item)];
            }
        }

        if ($category instanceof VisitorCategory) {
            return [$category->value];
        }

        if (is_string($category)) {
            try {
                return [VisitorCategory::fromName(Str::upper($category))->value];
            } catch (\Exception) {
            }
        }

        if (is_int($category)) {
            $category = VisitorCategory::tryFrom($category);
            return is_null($category) ? [] : [$category->value];
        }
        return array_values(array_unique($listCategories));
    }
}
