<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Enums\StatusVisit;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use OndrejVrto\Visitors\Enums\VisitorCategory;

/**
 * @method static StatusVisit forceIncrement()
 * @method static StatusVisit increment()
 * @method static self model(Visitable&Model $model)
 * @method static self withCrawlers()
 * @method static self withoutCrawlers()
 * @method static self fromIP(string $ipAddress)
 * @method static self addIpAddressToIgnoreList(array|string $ipAddress)
 * @method static self fromBrowserAgent(string $userAgent)
 * @method static self inCategory(VisitorCategory $category)
 * @method static self expiresAt(DateTimeInterface|int $expiresAt)
 * @method static self isCrawler(bool $status = true)
 * @method static self isPerson(bool $status = true)
 * @method static self inLanguage(string $language)
 * @method static self fromOperatingSystem(OperatingSystem $operatingSystem)
 * @method static self visitedAt(?DateTimeInterface $visitedAt = null)
 *
 * @see \OndrejVrto\Visitors\Visit
 */
class Visit extends Facade {
    protected static function getFacadeAccessor(): string {
        return 'visit';
    }
}
