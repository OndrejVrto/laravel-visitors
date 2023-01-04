<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Action\CheckCategory;
use OndrejVrto\Visitors\Action\CheckVisitable;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use OndrejVrto\Visitors\Models\VisitorsTraffic;
use OndrejVrto\Visitors\Traits\TrafficSettings;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use OndrejVrto\Visitors\Exceptions\InvalidClassParameter;

class Traffic {
    use TrafficSettings;

    /** @var string[] */
    private array $classes = [];

    /** @var array<string,string> */
    private array $orderBy = [];

    /** @var int[] */
    private array $categories = [];

    private ?bool $isCrawler = false;

    private ?bool $withRelationship = null;

    private ?int $limit = null;

    private int $countClasses = 0;

    private int $countCategories = 0;

    private ?Model $model = null;

    /**
     * @param Visitable|string|class-string|array<class-string> $visitable
     * @throws InvalidClassParameter
     */
    public function __construct(Visitable|string|array $visitable) {
        $this->classes = (new CheckVisitable())($visitable);

        if ($this->classes === []) {
            throw new InvalidClassParameter('Empty or bad parameter $visitable. Used class must implement Visitable contract.');
        }

        if ($visitable instanceof Visitable && $visitable instanceof Model) {
            $this->model = $visitable;
        }

        $this->orderBy = ['visit_total' => 'desc'];
    }

    /**
    * @param Visitable|string|class-string|array<class-string> $visitable
    */
    public function addModels(Visitable|string|array $visitable): self {
        $this->classes = [...$this->classes, ...(new CheckVisitable())($visitable)];
        if ($visitable instanceof Visitable && $visitable instanceof Model) {
            $this->model = $visitable;
        }
        return $this;
    }

    public function for(Visitable $visitable): self {
        if ($visitable instanceof Model) {
            $this->model = $visitable;
        }
        return $this;
    }

    /**
    * @param VisitorCategory|string|int|VisitorCategory[]|string[]|int[] $category
    */
    public function inCategories(VisitorCategory|string|int|array $category): self {
        $this->categories = [...$this->categories, ...(new CheckCategory())($category)];
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

    public function orderByTotal(string $direction = 'desc'): self {
        $this->setOrderBy('visit_total', $direction);
        return $this;
    }

    public function orderByLastDay(string $direction = 'desc'): self {
        $this->setOrderBy('visit_last_1_day', $direction);
        return $this;
    }

    public function orderByLast7Days(string $direction = 'desc'): self {
        $this->setOrderBy('visit_last_7_days', $direction);
        return $this;
    }

    public function orderByLast30Days(string $direction = 'desc'): self {
        $this->setOrderBy('visit_last_30_days', $direction);
        return $this;
    }

    public function orderByLast365Days(string $direction = 'desc'): self {
        $this->setOrderBy('visit_last_365_days', $direction);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'desc'): self {
        $this->setOrderBy($column, $direction);
        return $this;
    }

    private function setOrderBy(string $column, string $direction): void {
        unset($this->orderBy[$column]);
        $this->orderBy[$column] = $direction;
    }

    public function limit(int $value): self {
        $this->limit = (int) abs($value);
        return $this;
    }

    public function withRelationship(): self {
        $this->withRelationship = true;
        return $this;
    }

    private function handleConfigurations(): void {
        $this->classes = array_values(array_unique($this->classes));
        $this->countClasses = is_null($this->classes) ? 0 : count($this->classes);

        $this->categories = $this->trafficForCategories()
            ? array_values(array_unique($this->categories))
            : [];
        $this->countCategories = count($this->categories);

        if (!$this->trafficForCrawlersAndPersons()) {
            $this->isCrawler = false;
        }
    }

    private function getOrdersSql(): string {
        $orders = [];
        foreach ($this->orderBy as $type => $direction) {
            $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';
            $orders[] = sprintf("`%s` %s", $type, $direction);
        }
        return implode(", ", $orders);
    }

    private function queryToplist(): Builder {
        $this->handleConfigurations();

        return (new VisitorsTraffic())->query()
            ->whereNotNull('viewable_id')
            ->when($this->countClasses === 1, fn (Builder $q) => $q->where('viewable_type', '=', $this->classes[0]))
            ->when($this->countClasses > 1, fn (Builder $q) => $q->whereIn('viewable_type', $this->classes))
            ->when(is_null($this->isCrawler), fn (Builder $q) => $q->whereNull('is_crawler'))
            ->when(is_bool($this->isCrawler), fn (Builder $q) => $q->where('is_crawler', '=', $this->isCrawler))
            ->when($this->countCategories === 0, fn (Builder $q) => $q->whereNull('category'))
            ->when($this->countCategories === 1, fn (Builder $q) => $q->where('category', '=', $this->categories[0]))
            ->when($this->countCategories > 1, fn (Builder $q) => $q->whereIn('category', $this->categories))
            ->when($this->withRelationship == true, fn (Builder $q) => $q->with('viewable'))
            ->when(!is_null($this->limit), fn (Builder $q) => $q->limit($this->limit ?? 20))
            ->orderByRaw($this->getOrdersSql());
    }

    private function queryOneModel(): Builder {
        $this->handleConfigurations();

        if (is_null($this->model)) {
            throw new InvalidClassParameter('Empty or bad parameter $visitable. Used class must implement Visitable contract.');
        }

        return (new VisitorsTraffic())->query()
            ->whereMorphedTo('viewable', $this->model)
            ->when(is_null($this->isCrawler), fn (Builder $q) => $q->whereNull('is_crawler'))
            ->when(is_bool($this->isCrawler), fn (Builder $q) => $q->where('is_crawler', '=', $this->isCrawler))
            ->when($this->countCategories === 0, fn (Builder $q) => $q->whereNull('category'))
            ->when($this->countCategories > 0, fn (Builder $q) => $q->where('category', '=', $this->categories[0]))
            ->when($this->withRelationship == true, fn (Builder $q) => $q->with('viewable'));
    }

    /**
    * Execute the query as a "select" statement.
    *
    * @param  string[]|string  $columns
    * @return Collection|Model[]
    */
    public function get(array|string $columns = ['*']): Collection|Model {
        return $this->queryToplist()->get($columns);
    }

    /**
    * Paginate the given query.
    *
    * @param  int|null  $perPage
    * @param  string[] $columns
    * @param  string  $pageName
    * @param  int|null  $page
    * @return LengthAwarePaginator
    */
    public function paginate(?int $perPage = null, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator {
        return $this->queryToplist()->paginate($perPage, $columns, $pageName, $page);
    }

    /**
    * Execute the query and get the first result or throw an exception.
    *
    * @param  string[]|string  $columns
    * @return Model
    */
    public function firstOrFail(array|string $columns = ['*']): Model {
        return $this->queryOneModel()->firstOrFail($columns);
    }
}
