<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Traits\Setters;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use OndrejVrto\Visitors\Enums\StatusVisitor;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use OndrejVrto\Visitors\Models\VisitorsExpires;

class Visitor {

    use Setters;

    private bool $isCrawler;

    private Request $request;

    private bool $crawlerStorage;

    private ?string $country = null;

    private ?string $language = null;

    private ?string $userAgent = null;

    private ?string $ipAddress = null;

    private ?VisitorCategory $category = null;

    private DateTimeInterface $expiresAt;

    private DateTimeInterface $visitedAt;

    private OperatingSystem $operatingSystem;

    public function __construct(
        protected readonly Model $subject
    ) {
    }

    public function forceIncrement(): StatusVisitor {
        return $this->increment(false);
    }

    public function increment(bool $checkExpire = true): StatusVisitor {
        $this->handleInitialProperties();

        // dump($this);

        if ($this->isCrawler && ! $this->crawlerStorage) {
            return StatusVisitor::NOT_INCREMENT_CRAWLERS;
        }

        $this->handleRestProperties();

        // TODO: dorobit kontrolu vylucenych ip adries a rozsirit status kody

        if ($checkExpire) {
            $visitorExpire = VisitorsExpires::query()
                ->select(['id', 'expires_at'])
                ->whereMorphedTo('viewable', $this->subject)
                ->whereIpAddress($this->ipAddress)
                ->whereCategory($this->category)
                ->first();

            if ($visitorExpire !== null) {
                // dump($visitorExpire, $visitorExpire->expires_at, Carbon::now()->lessThan($visitorExpire->expires_at));

                if (Carbon::now()->lessThan($visitorExpire->expires_at)) {
                    return StatusVisitor::NOT_PASSED_EXPIRATION_TIME;
                }

                $visitorExpire->update(['expires_at' => $this->expiresAt]);
            } else {
                $this->subject->visitExpires()->create([
                    'ip_address' => $this->ipAddress,
                    'category'   => $this->category,
                    'expires_at' => $this->expiresAt,
                ]);
            }
        }

        $this->subject->visitData()->create([
            'category'         => $this->category,
            'is_crawler'       => $this->isCrawler,
            'country'          => $this->country,
            'language'         => $this->language,
            'operating_system' => $this->operatingSystem,
            'visited_at'       => $this->visitedAt,
        ]);

        return StatusVisitor::INCREMENT_OK;
    }

    private function handleInitialProperties(): void {
        /** @var \Illuminate\Http\Request $tempRequest */
        $request = request();
        if (! $request instanceof Request) {
            throw new \Exception("Bad request type.");
        }
        $this->request = $request;

        if (! isset($this->userAgent)) {
            $this->userAgent = $this->request->userAgent();
        }

        if (! isset($this->isCrawler)) {
            $this->isCrawler = (new CrawlerDetect())->isCrawler($this->userAgent);
        }

        if (! isset($this->crawlerStorage)) {
            $crawlerStorage = config('visitors.storage_request_from_crawlers_and_bots');
            $this->crawlerStorage = is_bool($crawlerStorage) && $crawlerStorage;
        }
    }

    private function handleRestProperties(): void {
        if (! isset($this->ipAddress)) {
            $this->ipAddress = $this->request->ip();
        }

        if (! isset($this->category)) {
            $defaultCategory = config('visitors.default_category');
            $this->category = (enum_exists($defaultCategory) && $defaultCategory instanceof \UnitEnum)
                ? $defaultCategory
                : VisitorCategory::UNDEFINED;
        }

        if (! isset($this->country)) {
            $countryCode = geoip($this->ipAddress)->getAttribute('iso_code');
            $this->country = is_null($countryCode)
                ? null
                : (is_string($countryCode) ? strtolower($countryCode) : null);
        }

        if (! isset($this->language)) {
            $language = $this->request->getLanguages();

            $this->language = $language === [] ? null : $language[0];
        }

        if (! isset($this->operatingSystem)) {
            $this->operatingSystem = $this->getVisitorOperatingSystem($this->userAgent);
        }

        if (! isset($this->visitedAt)) {
            $this->visitedAt = Carbon::now();
        }

        if (! isset($this->expiresAt)) {
            $expireTime = config('visitors.expires_time');
            $expireTime = is_int($expireTime) ? $expireTime : 15;
            $this->expiresAt($expireTime);
        }
    }

    private function getVisitorOperatingSystem(?string $agent): OperatingSystem {
        if (is_null($agent)) {
            return OperatingSystem::UNKNOWN;
        }

        foreach (OperatingSystem::cases() as $os) {
            $regex = $os->regexString();

            if ($regex === null) {
                continue;
            }

            if (preg_match($regex, $agent)) {
                return $os;
            }
        }

        return OperatingSystem::UNKNOWN;
    }
}
