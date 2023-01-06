<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Facades;

use Illuminate\Support\Facades\Facade;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Enums\StatusVisitor;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use OndrejVrto\Visitors\Enums\VisitorCategory;

/**
 * @method static self addIpAddressToIgnoreList(array|string $ipAddress)
 * @method static self expiresAt(DateTimeInterface|int $expiresAt)
 * @method static self fromBrowserAgent(string $userAgent)
 * @method static self fromCountry(string $country)
 * @method static self fromIP(string $ipAddress)
 * @method static self fromOperatingSystem(OperatingSystem $operatingSystem)
 * @method static self inCategory(VisitorCategory $category)
 * @method static self inLanguage(string $language)
 * @method static self isCrawler(bool $status = true)
 * @method static self isPerson(bool $status = true)
 * @method static self visitedAt(?DateTimeInterface $visitedAt = null)
 * @method static self withCrawlers()
 * @method static self withoutCrawlers()
 * @method static StatusVisitor forceIncrement(Visitable $visitable)
 * @method static StatusVisitor increment(Visitable $visitable)
 *
 * @see \OndrejVrto\Visitors\Visitor
 */
class Visitor extends Facade {
    protected static function getFacadeAccessor(): string {
        return 'visitor';
    }
}
