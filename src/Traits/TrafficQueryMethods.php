<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Action\CheckCategory;
use OndrejVrto\Visitors\Action\CheckVisitable;
use OndrejVrto\Visitors\Enums\VisitorCategory;

trait TrafficQueryMethods {
    use VisitorsSettings;

    /** @var string[]|null */
    private ?array $models = [];

    private Model|null $model = null;

    /** @var int[] */
    private array $categories = [];

    private ?int $category = null;

    private ?bool $isCrawler = null;

    private int $countClasses = 0;

    private int $countCategories = 0;

    private ?bool $withRelationship = null;

    private function handleConfigurations(): void {
        $this->countClasses = [] === $this->models ? 0 : count($this->models);

        $this->models = 0 === $this->countClasses
            ? [null]
            : array_values(array_unique($this->models));

        $this->categories = $this->trafficForCategories()
            ? array_values(array_unique($this->categories))
            : [];

        $this->category = [] === $this->categories
            ? null
            : $this->categories[0];

        $this->countCategories = count($this->categories);

        $this->isCrawler = $this->trafficForCrawlersAndPersons()
            ? $this->isCrawler
            : false;
    }

    public function inCategory(VisitorCategory|string|int $category): self {
        $this->categories = (new CheckCategory())($category);
        return $this;
    }

    public function forModel(Visitable|string $model): self {
        if ($model instanceof Model) {
            $this->model = $model;
        }
        $this->models = (new CheckVisitable())($model);

        return $this;
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
