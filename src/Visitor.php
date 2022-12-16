<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Enums\Category;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use OndrejVrto\Visitors\Enums\StatusVisitor;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use OndrejVrto\Visitors\Models\VisitorsExpires;

class Visitor {
    protected readonly Model $subject;

    private Request $request;

    private ?string $ipAddress = null;

    private ?string $userAgent = null;

    private ?Category $category = null;

    private bool $crawlerStorage;

    private DateTimeInterface $expiresAt;

    private bool $isCrawler;

    private ?string $country = null;

    private ?string $language = null;

    private OperatingSystem $operatingSystem;

    private DateTimeInterface $visitedAt;

    public function __construct(Model $subject) {
        $this->subject = $subject;
    }

    public function increment(bool $checkExpire = true): StatusVisitor {
        $this->handleProperties();

        // dump($this);

        if ($this->isCrawler && ! $this->crawlerStorage) {
            return StatusVisitor::NOT_INCREMENT;
        }

        if ($checkExpire) {
            $visitor = VisitorsExpires::query()
                ->select('expires_at')
                ->whereMorphedTo(
                    'viewable',
                    $this->subject
                )
                ->where('ip_address', $this->ipAddress)
                ->first();

            // dump($checkExpire, $visitor);

            if ($visitor) {
                if ($visitor->expires_at > now()) {
                    return StatusVisitor::NOT_PASSED_EXPIRATION_TIME;
                } else {
                    $visitor->update(['expires_at' => $this->expiresAt]);
                }
            } else {
                $this->subject->visitExpire()->create([
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

    public function forceIncrement(): StatusVisitor {
        return $this->increment(false);
    }

    public function inCategory(Category $category): self {
        $this->category = $category;

        return $this;
    }

    public function withCrawlers(): self {
        $this->crawlerStorage = true;

        return $this;
    }

    public function withoutCrawlers(): self {
        $this->crawlerStorage = false;

        return $this;
    }

    public function expiresAt(DateTimeInterface|int $expiresAt): self {
        $this->expiresAt = $expiresAt instanceof DateTimeInterface
            ? $expiresAt
            : Carbon::now()->addMinutes($expiresAt);

        return $this;
    }

    public function isCrawler(bool $status = false): self {
        $this->isCrawler = $status;

        return $this;
    }

    public function fromCountry(string $country): self {
        $this->country = $country;

        return $this;
    }

    public function inLanguage(string $language): self {
        $this->language = $language;

        return $this;
    }

    public function fromOperatingSystem(OperatingSystem $operatingSystem): self {
        $this->operatingSystem = $operatingSystem;

        return $this;
    }

    public function visitedAt(DateTimeInterface $visitedAt = null): self {
        $this->visitedAt = $visitedAt ?? Carbon::now();

        return $this;
    }

    private function handleProperties(): void {
        $this->handleRequest();

        if (! isset($this->category)) {
            $this->category = null;
        }

        if (! isset($this->crawlerStorage)) {
            $crawlerStorage = config('visitors.storage_request_from_crawlers_and_bots');
            $this->crawlerStorage = is_bool($crawlerStorage) && $crawlerStorage;
        }

        if (! isset($this->expiresAt)) {
            $remember = config('vvisitors.with_remember_expiration_for_all_ip');
            $remember = is_bool($remember) ? $remember : true;

            $expireTime = $remember ? config('visitors.expires_time') : 0;
            $expireTime = is_integer($expireTime) ? $expireTime : 15;

            $this->expiresAt($expireTime);
        }

        if (! isset($this->isCrawler)) {
            $this->isCrawler = (new CrawlerDetect())->isCrawler($this->userAgent);
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
    }

    private function handleRequest(): void {
        $tempRequest = request();

        if (! $tempRequest instanceof Request) {
            throw new \Exception("Bad request type.");
        }

        $this->request = $tempRequest;

        $this->ipAddress = $tempRequest->ip();

        $this->userAgent = $tempRequest->userAgent();
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
