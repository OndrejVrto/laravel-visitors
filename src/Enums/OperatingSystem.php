<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Enums;

enum OperatingSystem: int {
    case UNKNOWN       = 0;
    case WINDOWS       = 1;
    case IPHONE        = 2;
    case IPAD          = 3;
    case MACOS         = 4;
    case ANDROIDMOBILE = 5;
    case ANDROIDTABLET = 6;
    case ANDROID       = 7;
    case BLACKBERRY    = 8;
    case LINUX         = 9;

    public function label(): string {
        $label = match ($this) {
            self::UNKNOWN       => __('laravel-visitors.operating_system.UNKNOWN'),
            self::WINDOWS       => __('laravel-visitors.operating_system.WINDOWS'),
            self::IPHONE        => __('laravel-visitors.operating_system.IPHONE'),
            self::IPAD          => __('laravel-visitors.operating_system.IPAD'),
            self::MACOS         => __('laravel-visitors.operating_system.MACOS'),
            self::ANDROIDMOBILE => __('laravel-visitors.operating_system.ANDROIDMOBILE'),
            self::ANDROIDTABLET => __('laravel-visitors.operating_system.ANDROIDTABLET'),
            self::ANDROID       => __('laravel-visitors.operating_system.ANDROID'),
            self::BLACKBERRY    => __('laravel-visitors.operating_system.BLACKBERRY'),
            self::LINUX         => __('laravel-visitors.operating_system.LINUX'),
            default             => __('laravel-visitors.operating_system.DEFAULT'),
        };

        return is_string($label) ? $label : '';
    }

    public function regexString(): ?string {
        return match ($this) {
            self::UNKNOWN       => null,
            self::WINDOWS       => '/windows|win32|win16|win95|win64/i',
            self::IPHONE        => '/iphone/i',
            self::IPAD          => '/ipad/i',
            self::MACOS         => '/macintosh|mac os x|mac_powerpc/i',
            self::ANDROIDMOBILE => '/(?=.*mobile)android/i',
            self::ANDROIDTABLET => '/(?!.*mobile)android/i',
            self::ANDROID       => '/android/i',
            self::BLACKBERRY    => '/blackberry/i',
            self::LINUX         => '/linux/i',
            default             => '',
        };
    }
}
