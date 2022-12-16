<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Enums;

enum VisitorCategory: int {
    case UNDEFINED     = 0;
    case WEB           = 1;
    case API           = 2;
    case AUTHENTICATED = 3;
    case GUEST         = 4;
    case MANUAL        = 5;

    public function label(): string {
        return match ($this) {
            self::UNDEFINED     => 'Undefined',
            self::WEB           => 'From WEB requests',
            self::API           => 'From API requests',
            self::AUTHENTICATED => 'From Authenticated users',
            self::GUEST         => 'From Guests',
            self::MANUAL        => 'Custom category',
            default             => 'Unknown',
        };
    }
}
