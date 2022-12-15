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
        return match ($this)
        {
            self::UNKNOWN       => 'Unknown',
            self::WINDOWS       => 'Windows',
            self::IPHONE        => 'iPhone',
            self::IPAD          => 'iPad',
            self::MACOS         => 'MacOS',
            self::ANDROIDMOBILE => 'AndroidMobile',
            self::ANDROIDTABLET => 'AndroidTablet',
            self::ANDROID       => 'Android',
            self::BLACKBERRY    => 'BlackBerry',
            self::LINUX         => 'Linux',
            default             => 'Unknown',
        };
    }

    public function regexString(): ?string {
        return match ($this)
        {
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
