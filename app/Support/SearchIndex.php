<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserLibrary;
use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator as ConcreteLengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Laravel\Scout\Builder as ScoutBuilder;

final class SearchIndex
{
    /**
     * The class of the searched model.
     *
     * @var string $modelClass
     */
    protected string $modelClass;

    /**
     * The criteria the results match.
     *
     * @var SearchCriteria $criteria
     */
    protected SearchCriteria $criteria;

    /**
     * The query the plain index starts from.
     *
     * @var EloquentBuilder|Relation|null $base
     */
    protected EloquentBuilder|Relation|null $base = null;

    /**
     * The eager loads the results need.
     *
     * @var Closure|null $hydrate
     */
    protected ?Closure $hydrate = null;

    /**
     * The constraints of the plain index query.
     *
     * @var Closure[] $indexHooks
     */
    protected array $indexHooks = [];

    /**
     * The constraints of the search engine query.
     *
     * @var Closure[] $searchHooks
     */
    protected array $searchHooks = [];

    /**
     * The search engine filters every search carries.
     *
     * @var array $wheres
     */
    protected array $wheres = [];

    /**
     * The column the alphabetical index reads.
     *
     * @var string $letterColumn
     */
    protected string $letterColumn = 'original_title';

    /**
     * The translation relation the alphabetical index reads through.
     *
     * @var string|null $letterRelation
     */
    protected ?string $letterRelation = null;

    /**
     * The search engine attribute the search type filters.
     *
     * @var string $typeColumn
     */
    protected string $typeColumn = 'media_type_id';

    /**
     * The constraint the plain index applies for the search type.
     *
     * @var Closure|null $typeConstraint
     */
    protected ?Closure $typeConstraint = null;

    /**
     * The user whose library the results are limited to.
     *
     * @var User|null $libraryUser
     */
    protected ?User $libraryUser = null;

    /**
     * The library statuses the results are limited to.
     *
     * @var array $libraryStatuses
     */
    protected array $libraryStatuses = [];

    /**
     * Whether hidden library entries are left out.
     *
     * @var bool $excludesHidden
     */
    protected bool $excludesHidden = false;

    /**
     * Creates an index of the given model matching the given criteria.
     *
     * @param string         $modelClass
     * @param SearchCriteria $criteria
     */
    public function __construct(string $modelClass, SearchCriteria $criteria)
    {
        $this->modelClass = $modelClass;
        $this->criteria = $criteria;
    }

    /**
     * Starts the plain index from the given query.
     *
     * @param EloquentBuilder|Relation $query
     *
     * @return self
     */
    public function from(EloquentBuilder|Relation $query): self
    {
        $this->base = $query;

        return $this;
    }

    /**
     * Eager loads the results with the given callback.
     *
     * @param Closure $callback
     *
     * @return self
     */
    public function hydrate(Closure $callback): self
    {
        $this->hydrate = $callback;

        return $this;
    }

    /**
     * Constrains the plain index query with the given callback.
     *
     * @param Closure $callback
     *
     * @return self
     */
    public function index(Closure $callback): self
    {
        $this->indexHooks[] = $callback;

        return $this;
    }

    /**
     * Constrains the search engine query with the given callback.
     *
     * @param Closure $callback
     *
     * @return self
     */
    public function search(Closure $callback): self
    {
        $this->searchHooks[] = $callback;

        return $this;
    }

    /**
     * Filters every search on the given attribute.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return self
     */
    public function where(string $attribute, mixed $value): self
    {
        $this->wheres[$attribute] = $value;

        return $this;
    }

    /**
     * Reads the alphabetical index from the given column.
     *
     * @param string      $column
     * @param string|null $relation
     *
     * @return self
     */
    public function letter(string $column, ?string $relation = null): self
    {
        $this->letterColumn = $column;
        $this->letterRelation = $relation;

        return $this;
    }

    /**
     * Filters the search type on the given attribute.
     *
     * @param string       $column
     * @param Closure|null $constraint
     *
     * @return self
     */
    public function type(string $column, ?Closure $constraint = null): self
    {
        $this->typeColumn = $column;
        $this->typeConstraint = $constraint;

        return $this;
    }

    /**
     * Limits the results to the given user's library.
     *
     * @param User  $user
     * @param array $statuses
     * @param bool  $excludeHidden
     *
     * @return self
     */
    public function library(User $user, array $statuses = [], bool $excludeHidden = false): self
    {
        $this->libraryUser = $user;
        $this->libraryStatuses = $statuses;
        $this->excludesHidden = $excludeHidden;

        return $this;
    }

    /**
     * Paginates the results.
     *
     * @return LengthAwarePaginator
     */
    public function paginate(): LengthAwarePaginator
    {
        if (!$this->criteria->needsSearch()) {
            return $this->indexQuery()
                ->paginate($this->criteria->perPage);
        }

        if ($this->libraryUser !== null) {
            return $this->paginateLibrary();
        }

        return $this->searchQuery()
            ->paginate($this->criteria->perPage);
    }

    /**
     * The plain index query with the type and letter applied.
     *
     * @return EloquentBuilder|Relation
     */
    protected function indexQuery(): EloquentBuilder|Relation
    {
        $query = $this->base ?? $this->modelClass::query();

        if ($this->libraryUser !== null) {
            $query = $this->libraryUser
                ->whereTracked($this->modelClass)
                ->withoutIgnoreList()
                ->when($this->excludesHidden, function ($query) {
                    $query->where(UserLibrary::TABLE_NAME . '.is_hidden', '=', false);
                })
                ->wherePivotIn('status', $this->libraryStatuses);
        }

        if ($this->hydrate !== null) {
            $query->tap($this->hydrate);
        }

        if ($this->criteria->typeValue !== null) {
            if ($this->typeConstraint !== null) {
                ($this->typeConstraint)($query, $this->criteria->typeValue);
            } else {
                $query->where($this->typeColumn, '=', $this->criteria->typeValue);
            }
        }

        if ($this->criteria->letter !== '') {
            $this->constrainLetter($query);
        }

        foreach ($this->indexHooks as $hook) {
            $query->tap($hook);
        }

        return $query;
    }

    /**
     * The search engine query with the filters, orders, type and letter applied.
     *
     * @return ScoutBuilder
     */
    protected function searchQuery(): ScoutBuilder
    {
        [$wheres, $whereIns] = $this->engineFilters();

        $query = $this->modelClass::search($this->criteria->search);
        $query->wheres = $wheres;
        $query->whereIns = $whereIns;
        $query->orders = $this->criteria->orders();

        if ($this->hydrate !== null) {
            $query->query($this->hydrate);
        }

        foreach ($this->searchHooks as $hook) {
            $hook($query);
        }

        return $query;
    }

    /**
     * Paginates the search results within the user's library.
     *
     * @return LengthAwarePaginator
     */
    protected function paginateLibrary(): LengthAwarePaginator
    {
        [$wheres, $whereIns] = $this->engineFilters();

        $libraryIds = UserLibrary::where('user_id', '=', $this->libraryUser->id)
            ->where('trackable_type', '=', $this->modelClass)
            ->when(!empty($this->libraryStatuses), function ($query) {
                $query->whereIn('status', $this->libraryStatuses);
            })
            ->when($this->excludesHidden, function ($query) {
                $query->where('is_hidden', '=', false);
            })
            ->pluck('trackable_id')
            ->all();

        if (empty($libraryIds)) {
            return $this->emptyPaginator();
        }

        if ($this->criteria->search === '' && empty($wheres) && empty($whereIns)) {
            $matchedIds = $libraryIds;
        } else {
            $librarySet = array_flip($libraryIds);
            $cap = (int) config('scout.library_search_relevance_cap', 10000);

            $search = $this->modelClass::search($this->criteria->search)
                ->options(['attributesToRetrieve' => ['id']]);
            $search->wheres = $wheres;
            $search->whereIns = $whereIns;

            foreach ($this->searchHooks as $hook) {
                $hook($search);
            }

            $matchedIds = [];

            foreach ($search->take($cap)->raw()['hits'] ?? [] as $hit) {
                $id = $hit['id'] ?? null;

                if ($id !== null && isset($librarySet[$id])) {
                    $matchedIds[] = $id;
                }
            }

            if (empty($matchedIds)) {
                return $this->emptyPaginator();
            }
        }

        $query = $this->modelClass::whereIn('id', $matchedIds);

        if ($this->hydrate !== null) {
            $query->tap($this->hydrate);
        }

        $models = $query->get()->keyBy('id');
        $orders = $this->criteria->orders();

        if (empty($orders)) {
            $sorted = collect($matchedIds)
                ->map(fn ($id) => $models->get($id))
                ->filter()
                ->values();
        } else {
            $sorted = $models->values();

            foreach (array_reverse($orders) as $order) {
                $sorted = $order['direction'] === 'asc'
                    ? $sorted->sortBy($order['column'])
                    : $sorted->sortByDesc($order['column']);
            }

            $sorted = $sorted->values();
        }

        $page = max(1, Paginator::resolveCurrentPage());
        $perPage = $this->criteria->perPage;

        return new ConcreteLengthAwarePaginator(
            $sorted->slice(($page - 1) * $perPage, $perPage)->values(),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()],
        );
    }

    /**
     * The search engine filters the criteria, the type and the letter describe.
     *
     * @return array
     */
    protected function engineFilters(): array
    {
        [$wheres, $whereIns] = $this->criteria->wheres();

        $wheres = array_merge($wheres, $this->wheres);

        if ($this->criteria->letter !== '') {
            $wheres['letter'] = $this->criteria->letter;
        }

        if ($this->criteria->typeValue !== null) {
            $wheres[$this->typeColumn] = $this->criteria->typeValue;
        }

        return [$wheres, $whereIns];
    }

    /**
     * Limits the given query to the selected letter.
     *
     * @param EloquentBuilder|Relation $query
     *
     * @return void
     */
    protected function constrainLetter(EloquentBuilder|Relation $query): void
    {
        $letter = $this->criteria->letter;
        $column = $this->letterColumn;

        $constraint = function ($query) use ($letter, $column) {
            if ($letter === '.') {
                $query->whereRaw($column . ' REGEXP \'^[^a-zA-Z]*$\'');
            } else {
                $query->whereLike($column, $letter . '%');
            }
        };

        if ($this->letterRelation === null) {
            $constraint($query);
            return;
        }

        $query->whereRelation($this->letterRelation, function ($query) use ($constraint) {
            $query->where('locale', '=', 'en');
            $constraint($query);
        });
    }

    /**
     * A paginator without results.
     *
     * @return LengthAwarePaginator
     */
    protected function emptyPaginator(): LengthAwarePaginator
    {
        return new ConcreteLengthAwarePaginator(
            [],
            0,
            $this->criteria->perPage,
            max(1, Paginator::resolveCurrentPage()),
            ['path' => Paginator::resolveCurrentPath()],
        );
    }
}
