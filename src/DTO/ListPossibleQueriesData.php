<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\DTO;

class ListPossibleQueriesData {
    public function __construct(
        public readonly ?string $viewable_type = null,
        public readonly ?int    $viewable_id = null,
        public readonly ?bool   $is_crawler = null,
        public readonly ?int    $category = null,
    ) {
    }
}
