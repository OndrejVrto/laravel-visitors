<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\DTO;

class ListOptionData {
    public function __construct(
        public readonly ?string $viewable_type,
        public readonly ?int $viewable_id,
        public readonly ?bool $is_crawler,
        public readonly ?int $category,
    ) {
    }
}
