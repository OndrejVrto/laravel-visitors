<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use OndrejVrto\Visitors\Enums\StatusVisit;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Models\BaseVisitors;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use OndrejVrto\Visitors\Traits\VisitorSetters;
use OndrejVrto\Visitors\Models\VisitorsExpires;

class Visit {
    use VisitorSetters;

    protected Visitable $model;

    private Request $request;

    private bool $isCrawler;

    private bool $crawlerStorage;

    private bool $checkExpire = true;

    private ?string $country = null;

    private ?string $language = null;

    private ?string $userAgent = null;

    private ?string $ipAddress = null;

    /** @var string[] */
    private array $ignoredIpAddresses = [];

    private VisitorCategory $category;

    private DateTimeInterface $expiresAt;

    private DateTimeInterface $visitedAt;

    private OperatingSystem $operatingSystem;

    public function forceIncrement(Visitable $visitable): StatusVisit {
        $this->checkExpire = false;
        return $this->increment($visitable);
    }

    public function increment(Visitable $visitable): StatusVisit {
        if ($visitable instanceof Model) {
            $this->model = $visitable;
        } else {
            throw new \Exception("Class ".$visitable->getMorphClass()." must by ".Model::class." type.");
        }

        $this->handleInitialProperties();

        if ($this->isCrawler && ! $this->crawlerStorage) {
            return StatusVisit::NOT_INCREMENT_CRAWLERS;
        }

        if ($this->ipAddress !== null && collect($this->ignoredIpAddresses)->contains($this->ipAddress)) {
            return StatusVisit::NOT_INCREMENT_IP_ADDRESS;
        }

        $this->handleRestProperties();

        if ($this->checkExpire) {
            $statusExpire = $this->saveExpire();
            if ($statusExpire !== StatusVisit::INCREMENT_EXPIRATION_OK) {
                return $statusExpire;
            }
        }

        $statusData = $this->sevaData();
        if ($statusData !== StatusVisit::INCREMENT_DATA_OK) {
            return $statusData;
        }

        return StatusVisit::INCREMENT_OK;
    }

    private function handleInitialProperties(): void {
        $this->request = request();

        if (! isset($this->ipAddress)) {
            $this->ipAddress = $this->request->ip();
        }

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

        $defaultIgnoreIP = config('visitors.ignored_ip_addresses');
        if (is_array($defaultIgnoreIP) || is_string($defaultIgnoreIP)) {
            $this->addIpAddressToIgnoreList($defaultIgnoreIP);
        }
    }

    private function handleRestProperties(): void {
        if (! isset($this->category)) {
            $defaultCategory = config('visitors.default_category');
            $this->category = (is_string($defaultCategory) && VisitorCategory::isValidCase($defaultCategory))
                ? VisitorCategory::fromName($defaultCategory)
                : VisitorCategory::UNDEFINED;
        }

        if (! isset($this->country)) {
            $countryCode = geoip($this->ipAddress)->getAttribute('iso_code');
            $this->country = is_null($countryCode)
                ? null
                : (is_string($countryCode) ? substr(strtolower($countryCode), 0, 14) : null);
        }

        if (! isset($this->language)) {
            $language = $this->request->getLanguages();
            $this->language = $language === [] ? null : substr(strtolower($language[0]), 0, 14);
        }

        if (! isset($this->operatingSystem)) {
            $this->operatingSystem = $this->getVisitorOperatingSystem($this->userAgent);
        }

        if (! isset($this->visitedAt)) {
            $this->visitedAt = Carbon::now();
        }

        if (! isset($this->expiresAt)) {
            $expireTime = config('visitors.expires_time_for_visit');
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

    private function saveExpire(): StatusVisit {
        $visitorExpire = VisitorsExpires::query()
            ->select(['id', 'expires_at'])
            ->whereMorphedTo('viewable', $this->model->getMorphClass())
            ->where('ip_address', $this->ipAddress)
            ->where('category', $this->category)
            ->first();

        if ($visitorExpire instanceof BaseVisitors) {
            if (Carbon::now()->lessThan($visitorExpire->getAttributeValue('expires_at'))) {
                return StatusVisit::NOT_PASSED_EXPIRATION_TIME;
            }

            $status = $visitorExpire
                ->query()
                ->update(['expires_at' => $this->expiresAt]);
        } else {
            $model = $this->model
                ->visitExpires()
                ->create([
                    'ip_address' => $this->ipAddress,
                    'category'   => $this->category,
                    'expires_at' => $this->expiresAt,
                ]);

            $status = $model instanceof VisitorsExpires;
        }

        return $status
            ? StatusVisit::INCREMENT_EXPIRATION_OK
            : StatusVisit::INCREMENT_EXPIRATION_FAILED;
    }

    private function sevaData(): StatusVisit {
        $status = $this->model
            ->visitData()
            ->create([
                'category'         => $this->category,
                'is_crawler'       => $this->isCrawler,
                'country'          => $this->country,
                'language'         => $this->language,
                'operating_system' => $this->operatingSystem,
                'visited_at'       => $this->visitedAt,
            ]);

        return $status instanceof VisitorsData
            ? StatusVisit::INCREMENT_DATA_OK
            : StatusVisit::INCREMENT_DATA_FAILED;
    }
}
