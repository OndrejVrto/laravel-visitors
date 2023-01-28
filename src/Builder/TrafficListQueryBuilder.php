<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Builder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Action\CheckCategory;
use OndrejVrto\Visitors\Action\CheckVisitable;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use OndrejVrto\Visitors\Models\VisitorsTraffic;
use OndrejVrto\Visitors\Traits\TrafficQueryMethods;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use OndrejVrto\Visitors\Exceptions\InvalidClassParameter;

class TrafficListQueryBuilder {
    use TrafficQueryMethods;

    /** @var string[] */
    private array $classes = [];

    /** @var array<string,string> */
    private array $orderBy = [];

    /** @var int[] */
    private array $categories = [];

    private ?int $limit = null;

    private int $countClasses = 0;

    private int $countCategories = 0;

    /**
     * @param Visitable|string|class-string|array<class-string>|array<string> $visitable
     * @throws InvalidClassParameter
     */
    public function __construct(Visitable|string|array $visitable) {
        $this->classes = (new CheckVisitable())($visitable);

        if ([] === $this->classes) {
            throw new InvalidClassParameter('Empty or bad parameter $visitable. Used class must implement Visitable contract.');
        }

        $this->orderBy = ['visit_total' => 'desc'];
    }

    /**
     * @param Visitable|string|class-string|array<class-string> $visitable
     */
    public function addModels(Visitable|string|array $visitable): self {
        $this->classes = [...$this->classes, ...(new CheckVisitable())($visitable)];

        return $this;
    }

    /**
     * @param VisitorCategory|string|int|VisitorCategory[]|string[]|int[] $category
     */
    public function inCategories(VisitorCategory|string|int|array $category): self {
        $this->categories = [...$this->categories, ...(new CheckCategory())($category)];
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
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';
        $this->orderBy[$column] = $direction;
    }

    private function getOrdersSql(): string {
        $orders = [];
        foreach ($this->orderBy as $type => $direction) {
            $orders[] = sprintf("`%s` %s", $type, $direction);
        }
        return implode(", ", $orders);
    }

    public function limit(int $value): self {
        $this->limit = (int) abs($value);
        return $this;
    }

    private function handleConfigurations(): void {
        $this->classes = array_values(array_unique($this->classes));
        $this->countClasses = [] === $this->classes ? 0 : count($this->classes);

        $this->categories = $this->trafficForCategories()
            ? array_values(array_unique($this->categories))
            : [];
        $this->countCategories = count($this->categories);

        $this->isCrawler = $this->trafficForCrawlersAndPersons()
            ? $this->isCrawler
            : false;
    }

    private function query(): Builder {
        $this->handleConfigurations();

        return VisitorsTraffic::query()
            ->whereNotNull('viewable_id')
            ->where('is_crawler', '=', $this->isCrawler)
            ->when(1 === $this->countClasses, fn (Builder $q) => $q->where('viewable_type', '=', $this->classes[0]))
            ->when($this->countClasses > 1, fn (Builder $q) => $q->whereIn('viewable_type', $this->classes))
            ->when(0 === $this->countCategories, fn (Builder $q) => $q->whereNull('category'))
            ->when(1 === $this->countCategories, fn (Builder $q) => $q->where('category', '=', $this->categories[0]))
            ->when($this->countCategories > 1, fn (Builder $q) => $q->whereIn('category', $this->categories))
            ->when(true === $this->withRelationship, fn (Builder $q) => $q->with('viewable'))
            ->unless(null === $this->limit, fn (Builder $q) => $q->limit($this->limit ?? 20))
            ->orderByRaw($this->getOrdersSql());
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  string[]|string  $columns
     * @return Collection|Model[]
     */
    public function get(array|string $columns = ['*']): Collection|Model {
        return $this->query()->get($columns);
    }

    /**
     * Paginate the given query.
     *
     * @param  string[] $columns
     */
    public function paginate(?int $perPage = null, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator {
        return $this->query()->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Execute the query and get the first result.
     *
     * @param  string[]|string  $columns
     */
    public function first(array|string $columns = ['*']): ?Model {
        return $this->query()->first($columns);
    }
}
