<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Enums;

enum StatusVisitor {
    case INCREMENT_OK;
    case NOT_PASSED_EXPIRATION_TIME;
    case NOT_INCREMENT_CRAWLERS;
    case NOT_INCREMENT_IP_ADDRESS;
}
