<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Builder;

trait TrafficQueryMethods {
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
}
