<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

trait TrafficQueryMethods {
    use VisitorsSettings;

    private ?bool $isCrawler = null;

    private ?bool $withRelationship = null;

    private function handleConfigurations(): void {
        $this->category = $this->trafficForCategories()
            ? $this->category
            : null;

        $this->isCrawler = $this->trafficForCrawlersAndPersons()
            ? $this->isCrawler
            : false;
    }

    public function withRelationship(): self {
        $this->withRelationship = true;
        return $this;
    }

    public function visitedByPersons(): self {
        $this->isCrawler = false;
        return $this;
    }

    public function visitedByCrawlers(): self {
        $this->isCrawler = true;
        return $this;
    }

    public function visitedByCrawlersAndPersons(): self {
        $this->isCrawler = null;
        return $this;
    }

    public function toSql(): string {
        return $this->query()->toSql();
    }

    public function dump(): self {
        $this->query()->dump();

        return $this;
    }

    public function dd(): never {
        $this->query()->dd();
    }
}
