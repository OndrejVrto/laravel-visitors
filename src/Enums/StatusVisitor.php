<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Enums;

enum StatusVisitor {
    case INCREMENT_OK;
    case NOT_INCREMENT;
    case NOT_PASSED_EXPIRATION_TIME;
}
