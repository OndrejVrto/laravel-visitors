<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use OndrejVrto\Visitors\Enums\StatusVisit;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Traits\VisitSetters;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use OndrejVrto\Visitors\Models\VisitorsExpires;
use OndrejVrto\Visitors\Traits\VisitorsSettings;

final class Visit {
    use VisitSetters;
    use VisitorsSettings;

    public function forceIncrement(): StatusVisit {
        $this->checkExpire = false;
        return $this->increment();
    }

    public function increment(): StatusVisit {
        $this->resolveInitialProperties();

        if ($this->isCrawler && ! $this->crawlerStorage) {
            return StatusVisit::NOT_INCREMENT_CRAWLERS;
        }

        if (null !== $this->ipAddress && (new Collection($this->ignoredIpAddresses))->contains($this->ipAddress)) {
            return StatusVisit::NOT_INCREMENT_IP_ADDRESS;
        }

        $this->resolveRestProperties();

        if ($this->checkExpire) {
            $statusExpire = $this->saveExpire();
            if (StatusVisit::INCREMENT_EXPIRATION_OK !== $statusExpire) {
                return $statusExpire;
            }
        }

        $statusData = $this->sevaData();
        if (StatusVisit::INCREMENT_DATA_OK !== $statusData) {
            return $statusData;
        }

        return StatusVisit::INCREMENT_OK;
    }

    private function resolveInitialProperties(): void {
        if ( ! isset($this->model)) {
            throw new Exception('Model must be set.');
        }

        $this->request = app('request');

        if ( ! isset($this->ipAddress)) {
            $this->ipAddress = $this->request->ip();
        }

        if ( ! isset($this->userAgent)) {
            $this->userAgent = $this->request->userAgent();
        }

        if ( ! isset($this->isCrawler)) {
            $this->isCrawler = (new CrawlerDetect())->isCrawler($this->userAgent);
        }

        if ( ! isset($this->crawlerStorage)) {
            $this->crawlerStorage = $this->defaultStorageCrawlersRequests();
        }

        $this->addIpAddressToIgnoreList($this->defaultVisitorsIgnoreIPList());
    }

    private function resolveRestProperties(): void {
        if ( ! isset($this->category)) {
            $this->category = $this->defaultVisitorsCategory();
        }

        if ( ! isset($this->language)) {
            $language = $this->request->getLanguages();
            $this->language = [] === $language ? null : mb_substr(mb_strtolower($language[0]), 0, 14);
        }

        if ( ! isset($this->operatingSystem)) {
            $this->operatingSystem = $this->getVisitorOperatingSystem($this->userAgent);
        }

        if ( ! isset($this->visitedAt)) {
            $this->visitedAt = Carbon::now();
        }

        if ( ! isset($this->expiresAt)) {
            $this->expiresAt($this->defaultVisitorsExpirationTime());
        }
    }

    private function getVisitorOperatingSystem(?string $agent): OperatingSystem {
        if (null === $agent) {
            return OperatingSystem::UNKNOWN;
        }

        foreach (OperatingSystem::cases() as $os) {
            $regex = $os->regexString();

            if (null === $regex) {
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
            ->select(['expires_at'])
            ->whereMorphedTo('viewable', $this->model)
            ->where('ip_address', $this->ipAddress)
            ->where('category', $this->category)
            ->first();

        if ($visitorExpire instanceof VisitorsExpires) {
            if (Carbon::now()->lessThan($visitorExpire->getAttributeValue('expires_at'))) {
                return StatusVisit::NOT_PASSED_EXPIRATION_TIME;
            }

            $status = (bool) $visitorExpire
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
                'language'         => $this->language,
                'operating_system' => $this->operatingSystem,
                'visited_at'       => $this->visitedAt,
            ]);

        return $status instanceof VisitorsData
            ? StatusVisit::INCREMENT_DATA_OK
            : StatusVisit::INCREMENT_DATA_FAILED;
    }
}
