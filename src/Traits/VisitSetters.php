<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use OndrejVrto\Visitors\Enums\VisitorCategory;

trait VisitSetters {
    protected Visitable&Model $model;

    private Request $request;

    private bool $isCrawler;

    private bool $crawlerStorage;

    private bool $checkExpire = true;

    private ?string $language = null;

    private ?string $userAgent = null;

    private ?string $ipAddress = null;

    /** @var string[] */
    private array $ignoredIpAddresses = [];

    private VisitorCategory $category;

    private DateTimeInterface $expiresAt;

    private DateTimeInterface $visitedAt;

    private OperatingSystem $operatingSystem;

    public function model(Visitable&Model $model): self {
        $this->model = $model;

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

    public function fromIP(string $ipAddress): self {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /** @param string|string[] $ipAddress */
    public function addIpAddressToIgnoreList(string|array $ipAddress): self {
        if (is_array($ipAddress)) {
            $this->ignoredIpAddresses = array_merge($this->ignoredIpAddresses, $ipAddress);
        } elseif (is_string($ipAddress)) {
            $this->ignoredIpAddresses[] = $ipAddress;
        }
        $this->ignoredIpAddresses = array_unique($this->ignoredIpAddresses);

        return $this;
    }

    public function fromBrowserAgent(string $userAgent): self {
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

    public function isCrawler(bool $status = true): self {
        $this->isCrawler = $status;

        return $this;
    }

    public function isPerson(bool $status = true): self {
        $this->isCrawler = ! $status;

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
