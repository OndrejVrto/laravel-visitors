<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Enums;

enum StatusVisit {
    case INCREMENT_OK;

    case INCREMENT_DATA_OK;
    case INCREMENT_DATA_FAILED;
    case INCREMENT_EXPIRATION_OK;
    case INCREMENT_EXPIRATION_FAILED;

    case NOT_PASSED_EXPIRATION_TIME;
    case NOT_INCREMENT_CRAWLERS;
    case NOT_INCREMENT_IP_ADDRESS;
}
