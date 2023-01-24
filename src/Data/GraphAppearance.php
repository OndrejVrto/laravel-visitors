<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Data;

class GraphAppearance {
    /** @param string[] $colors */
    public function __construct(
        public readonly ?array $colors = null,
        public readonly ?int $width_svg = null,
        public readonly ?int $height_svg = null,
        public readonly ?int $stroke_width = null,
        public readonly ?int $maximum_days = null,
        public readonly ?bool $order_reverse = null,
        public readonly ?int $maximum_value_lock = null,
    ) {
    }
}
