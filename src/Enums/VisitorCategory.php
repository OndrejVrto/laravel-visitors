<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Enums;

use EnumHelper\EnumValidatableCase;
use EnumHelper\EnumRestorableFromName;

enum VisitorCategory: int {
    use EnumRestorableFromName;
    use EnumValidatableCase;

    case UNDEFINED     = 0;
    case WEB           = 1;
    case API           = 2;
    case AUTHENTICATED = 3;
    case GUEST         = 4;
    case MANUAL        = 5;
    case CUSTOM_01     = 101;
    case CUSTOM_02     = 102;
    case CUSTOM_03     = 103;
    case CUSTOM_04     = 104;
    case CUSTOM_05     = 105;
    case CUSTOM_06     = 106;
    case CUSTOM_07     = 107;
    case CUSTOM_08     = 108;
    case CUSTOM_09     = 109;
    case CUSTOM_10     = 110;

    public function label(): string {
        $label = match ($this) {
            self::UNDEFINED     => __('laravel-visitors.category_labels.UNDEFINED'),
            self::WEB           => __('laravel-visitors.category_labels.WEB'),
            self::API           => __('laravel-visitors.category_labels.API'),
            self::AUTHENTICATED => __('laravel-visitors.category_labels.AUTHENTICATED'),
            self::GUEST         => __('laravel-visitors.category_labels.GUEST'),
            self::MANUAL        => __('laravel-visitors.category_labels.MANUAL'),
            self::CUSTOM_01     => __('laravel-visitors.category_labels.CUSTOM_01'),
            self::CUSTOM_02     => __('laravel-visitors.category_labels.CUSTOM_02'),
            self::CUSTOM_03     => __('laravel-visitors.category_labels.CUSTOM_03'),
            self::CUSTOM_04     => __('laravel-visitors.category_labels.CUSTOM_04'),
            self::CUSTOM_05     => __('laravel-visitors.category_labels.CUSTOM_05'),
            self::CUSTOM_06     => __('laravel-visitors.category_labels.CUSTOM_06'),
            self::CUSTOM_07     => __('laravel-visitors.category_labels.CUSTOM_07'),
            self::CUSTOM_08     => __('laravel-visitors.category_labels.CUSTOM_08'),
            self::CUSTOM_09     => __('laravel-visitors.category_labels.CUSTOM_09'),
            self::CUSTOM_10     => __('laravel-visitors.category_labels.CUSTOM_10'),
            default             => __('laravel-visitors.category_labels.DEFAULT'),
        };

        return is_string($label) ? $label : '';
    }
}
