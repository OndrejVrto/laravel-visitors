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

    /** @var array<string,string> */
    private array $orderBy = ['visit_total' => 'desc'];

    private ?int $limit = null;

    /**
     * @param Visitable|string|class-string|array<class-string> $models
     * @throws InvalidClassParameter
     */
    public function addModels(Visitable|string|array $models): self {
        $this->models = [...$this->models, ...(new CheckVisitable())($models)];

        if ([] === $this->models) {
            throw new InvalidClassParameter('Empty or bad parameter $visitable. Used class must implement Visitable contract.');
        }

        return $this;
    }

    /**
     * @param VisitorCategory|string|int|VisitorCategory[]|string[]|int[] $category
     */
    public function addCategories(VisitorCategory|string|int|array $category): self {
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

    private function query(): Builder {
        $this->handleConfigurations();

        return VisitorsTraffic::query()
            ->whereNotNull('viewable_id')
            ->where('is_crawler', '=', $this->isCrawler)
            ->when(1 === $this->countClasses, fn (Builder $q) => $q->where('viewable_type', '=', $this->models[0]))
            ->when($this->countClasses > 1, fn (Builder $q) => $q->whereIn('viewable_type', $this->models))
            ->when(0 === $this->countCategories, fn (Builder $q) => $q->whereNull('category'))
            ->when(1 === $this->countCategories, fn (Builder $q) => $q->where('category', '=', $this->categories[0]))
            ->when($this->countCategories > 1, fn (Builder $q) => $q->whereIn('category', $this->categories))
            ->when(true === $this->withRelationship, fn (Builder $q) => $q->with('viewable'))
            ->unless(null === $this->limit, fn (Builder $q) => $q->limit($this->limit ?? 20))
            ->orderByRaw($this->getOrdersSql());
    }

    public function limit(int $value): self {
        $this->limit = (int) abs($value);
        return $this;
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
