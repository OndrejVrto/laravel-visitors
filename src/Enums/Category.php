<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Enums;

use OndrejVrto\Visitors\Contracts\CategoryContract;

enum Category: int implements CategoryContract {
    case WEB           = 1;
    case API           = 2;
    case AUTHENTICATED = 3;
    case GUEST         = 4;
    case MANUAL        = 5;

    public function label(): string {
        return match ($this)
        {
            self::WEB           => 'From WEB requests',
            self::API           => 'From API requests',
            self::AUTHENTICATED => 'Authenticated users',
            self::GUEST         => 'Guests',
            self::MANUAL        => 'Custom category',
            default             => 'Unknown',
        };
    }
}
