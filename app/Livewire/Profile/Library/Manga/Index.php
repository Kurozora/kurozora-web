<?php

namespace App\Livewire\Profile\Library\Manga;

use App\Enums\UserLibraryStatus;
use App\Models\Episode;
use App\Models\Manga;
use App\Models\User;
use App\Models\UserLibrary;
use App\Traits\Livewire\WithMangaSearch;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Laravel\Scout\Builder as ScoutBuilder;
use Livewire\Component;

class Index extends Component
{
    use WithMangaSearch {
        queryString as protected parentQueryString;
        setFilterableAttributes as protected parentSetFilterableAttributes;
    }

    /**
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

    /**
     * The status of the library.
     *
     * @var string $status
     */
    public string $status = '';

    /**
     * The query strings of the component.
     *
     * @return string[]
     */
    protected function queryString() : array
    {
        $queryString = $this->parentQueryString();
        $queryString[] = 'status';
        return $queryString;
    }

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param User $user
     * @return void
     */
    public function mount(User $user): void
    {
        $this->user = $user;

        $status = str($this->status)->title();
        $status = str_replace('-', '', $status);

        if (!UserLibraryStatus::hasKey($status)) {
            $this->status = 'reading';
        }
    }

    /**
     * Sets the property to load the page.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Redirect the user to a random model.
     *
     * @return void
     * @throws InvalidEnumKeyException
     * @throws InvalidEnumMemberException
     */
    public function randomManga(): void
    {
        // Get library status
        $status = str_replace('-', '', $this->status);
        $userLibraryStatus = UserLibraryStatus::fromKey($status);

        $manga = $this->user
            ->whereTracked(Manga::class)
            ->wherePivot('status', $userLibraryStatus->value)
            ->withoutIgnoreList()
            ->inRandomOrder()
            ->first();
        $this->redirectRoute('manga.details', $manga);
    }

    /**
     * The computed search results property.
     *
     * @return ?LengthAwarePaginator
     * @throws InvalidEnumKeyException
     * @throws InvalidEnumMemberException
     */
    public function getSearchResultsProperty(): ?LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return null;
        }

        // Order
        $orders = [];
        foreach ($this->order as $attribute => $order) {
            $attribute = str_replace(':', '.', $attribute);
            $selected = $order['selected'];

            if (!empty($selected)) {
                $orders[] = [
                    'column' => $attribute,
                    'direction' => $selected,
                ];
            }
        }

        // Filter
        $wheres = [];
        $whereIns = [];
        foreach ($this->filter as $attribute => $filter) {
            if ($attribute == 'library_status') {
                continue;
            }

            $attribute = str_replace(':', '.', $attribute);
            $selected = $filter['selected'];
            $type = $filter['type'];

            if ((is_numeric($selected) && $selected >= 0) || !empty($selected)) {
                if ($type === 'multiselect') {
                    $whereIns[$attribute] = $selected;
                } else {
                    $wheres[$attribute] = match ($type) {
                        'date' => Carbon::createFromFormat('Y-m-d', $selected)
                            ?->setTime(0, 0)
                            ->timestamp,
                        'time' => $selected . ':00',
                        'double' => number_format($selected, 2, '.', ''),
                        default => $selected,
                    };
                }
            }
        }

        // Get library status
        $upperCaseStatus = implode('-', array_map('ucfirst', explode('-', $this->status)));
        $status = str_replace('-', '', $upperCaseStatus);
        $userLibraryStatus = UserLibraryStatus::fromKey($status);

        // If no search was performed, return all manga
        if (empty($this->search) && empty($wheres) && empty($whereIns)) {
            $models = $this->user
                ->whereTracked(static::$searchModel)
                ->withoutIgnoreList()
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->when(!empty($this->typeValue), function (EloquentBuilder $query) {
                    $query->where($this->typeColumn(), '=', $this->typeValue);
                })
                ->when(!empty($this->letter), function (EloquentBuilder $query) {
                    if (static::$searchModel === Episode::class) {
                        $query->whereRelation('translation', function ($query) {
                            $query->where('locale', '=', 'en');

                            if ($this->letter == '.') {
                                $query->whereRaw($this->letterIndexColumn() . ' REGEXP \'^[^a-zA-Z]*$\'');
                            } else {
                                $query->whereLike($this->letterIndexColumn(), $this->letter . '%');
                            }
                        });
                    } else {
                        if ($this->letter == '.') {
                            $query->whereRaw($this->letterIndexColumn() . ' REGEXP \'^[^a-zA-Z]*$\'');
                        } else {
                            $query->whereLike($this->letterIndexColumn(), $this->letter . '%');
                        }
                    }
                })
                ->wherePivot('status', $userLibraryStatus->value);
            return $models->paginate($this->perPage);
        }

        // Search
        $modelIDs = collect(UserLibrary::search($this->search)
            ->when(!empty($this->letter), function (ScoutBuilder $query) {
                $query->where('trackable.letter', $this->letter);
            })
            ->where('user_id', $this->user->id)
            ->where('trackable_type', addslashes(static::$searchModel))
            ->where('status', $userLibraryStatus->value)
            ->simplePaginateRaw(perPage: 2000, page: 1)
            ->items()['hits'] ?? [])
            ->pluck('trackable_id')
            ->toArray();
        $whereIns['id'] = $modelIDs;

        if (!empty($this->typeValue)) {
            $wheres[$this->typeColumn()] = $this->typeValue;
        }

        $models = static::$searchModel::search($this->search);
        $models->wheres = $wheres;
        $models->whereIns = $whereIns;
        $models->orders = $orders;
        $models = $this->searchQuery($models);

        // Paginate
        return $models->paginate($this->perPage);
    }

    /**
     * Build a 'search' query for the given resource.
     *
     * @param ScoutBuilder $query
     *
     * @return ScoutBuilder
     */
    public function searchQuery(ScoutBuilder $query): ScoutBuilder
    {
        return $query->query(function (EloquentBuilder $query) {
            $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                });
        });
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return array
     */
    public function setFilterableAttributes(): array
    {
        $filterableAttributes = $this->parentSetFilterableAttributes();
        unset($filterableAttributes['library_status']);
        return $filterableAttributes;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.library.manga.index');
    }
}
