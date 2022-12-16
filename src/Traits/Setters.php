<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

use Carbon\Carbon;
use DateTimeInterface;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use OndrejVrto\Visitors\Enums\VisitorCategory;

trait Setters {
    public function withCrawlers(): self {
        $this->crawlerStorage = true;

        return $this;
    }

    public function withoutCrawlers(): self {
        $this->crawlerStorage = false;

        return $this;
    }

    public function fromIP(?string $ipAddress = null): self {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function fromBrowserAgent(?string $userAgent): self {
        $this->userAgent = $userAgent;

        return $this;
    }

    public function inCategory(VisitorCategory $category): self {
        $this->category = $category;

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
}
