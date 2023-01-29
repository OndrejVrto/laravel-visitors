<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Data;

class GraphProperties {
    /** @param string[] $colors */
    public function __construct(
        public readonly array  $colors = [],
        public readonly ?int   $width_svg = null,
        public readonly ?int   $height_svg = null,
        public readonly ?float $stroke_width = null,
        public readonly ?int   $maximum_days = null,
        public readonly bool   $order_reverse = false,
        public readonly ?int   $maximum_value_lock = null,
    ) {
    }
}
