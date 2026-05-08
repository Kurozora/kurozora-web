<?php

namespace App\Livewire\Browse\Continuing;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Manga;
use App\Models\MediaType;
use App\Traits\Livewire\WithSearch;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;
use Livewire\Component;

class Index extends Component
{
    use WithSearch;

    /**
     * The library kind being viewed.
     *
     * @var int $kind
     */
    public int $kind = UserLibraryKind::Anime;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param int $kind
     *
     * @return void
     */
    public function mount(int $kind): void
    {
        $this->kind = $kind;
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
     * Redirects the user to a random continuing item.
     *
     * @return void
     */
    public function randomItem(): void
    {
        $modelClass = $this->modelClass();
        $item = $modelClass::where([
            ['status_id', '=', 3],
            [$this->dateColumn(), '<=', season_of_year()->startDate()->toDateString()],
        ])->randomFirst();

        match ($this->kind) {
            UserLibraryKind::Anime => $this->redirectRoute('anime.details', $item),
            UserLibraryKind::Manga => $this->redirectRoute('manga.details', $item),
        };
    }

    /**
     * The computed search results property.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getSearchResultsProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

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

        $modelClass = $this->modelClass();
        $dateColumn = $this->dateColumn();
        $hydrate = $this->hydrationCallback();

        // Season of Year is calculated on month level, so the year value is `0`.
        // Here we require the start date of the current year's season, so we
        // manually set the year.
        $seasonStartDate = season_of_year()
            ->startDate()
            ->setYear(now()->year);

        if (empty($this->search) && empty($wheres) && empty($whereIns) && empty($orders)) {
            $models = $modelClass::query()
                ->where([
                    [$modelClass::TABLE_NAME . '.status_id', '=', 3],
                    [$modelClass::TABLE_NAME . '.' . $dateColumn, '<=', $seasonStartDate->toDateString()],
                ])
                ->orderBy($dateColumn, 'desc')
                ->tap($hydrate)
                ->when(!empty($this->typeValue), function (EloquentBuilder $query) {
                    $query->where($this->typeColumn(), '=', $this->typeValue);
                })
                ->when(!empty($this->letter), function (EloquentBuilder $query) {
                    if ($this->letter == '.') {
                        $query->whereRaw($this->letterIndexColumn() . ' REGEXP \'^[^a-zA-Z]*$\'');
                    } else {
                        $query->whereLike($this->letterIndexColumn(), $this->letter . '%');
                    }
                });

            return $models->paginate($this->perPage);
        }

        if (!empty($this->letter)) {
            $wheres['letter'] = $this->letter;
        }

        if (!empty($this->typeValue)) {
            $wheres[$this->typeColumn()] = $this->typeValue;
        }

        $wheres['status_id'] = 3;
        $wheres[$dateColumn] = ['<=', $seasonStartDate->timestamp];

        $models = $modelClass::search($this->search);
        $models->wheres = $wheres;
        $models->whereIns = $whereIns;
        $models->orders = $orders;
        $models = $this->searchQuery($models);

        return $models->paginate($this->perPage);
    }

    /**
     * Returns the trackable hydration eager-load callback.
     *
     * @return Closure
     */
    protected function hydrationCallback(): Closure
    {
        $authUser = auth()->user();

        return function (EloquentBuilder $query) use ($authUser) {
            $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
                ->when($authUser !== null, function (EloquentBuilder $query) use ($authUser) {
                    $query->with(['library' => function ($query) use ($authUser) {
                        $query->where('user_id', '=', $authUser->id);
                    }]);
                });
        };
    }

    /**
     * Routes the trackable hydration through the shared eager-load callback.
     *
     * @param ScoutBuilder $query
     *
     * @return ScoutBuilder
     */
    public function searchQuery(ScoutBuilder $query): ScoutBuilder
    {
        return $query->query($this->hydrationCallback());
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return array
     */
    public function setOrderableAttributes(): array
    {
        $modelClass = $this->modelClass();
        return $modelClass::webSearchOrders();
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return array
     */
    public function setFilterableAttributes(): array
    {
        $modelClass = $this->modelClass();
        return $modelClass::webSearchFilters();
    }

    /**
     * Set the search types of the model.
     *
     * @return array
     */
    public function setSearchTypes(): array
    {
        return MediaType::where('type', '=', $this->kindSlug())
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend(__('All'), 'all')
            ->toArray();
    }

    /**
     * Returns the model class for the active library kind.
     *
     * @return string
     */
    protected function modelClass(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => Anime::class,
            UserLibraryKind::Manga => Manga::class,
        };
    }

    /**
     * Returns the date column used for the continuing-season filter.
     *
     * @return string
     */
    protected function dateColumn(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime, UserLibraryKind::Manga => 'started_at',
        };
    }

    /**
     * Returns the media-type slug used to scope MediaType lookups.
     *
     * @return string
     */
    protected function kindSlug(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => 'anime',
            UserLibraryKind::Manga => 'manga',
        };
    }

    /**
     * Returns the localized noun used in og:title and document title.
     *
     * @return string
     */
    public function getOgTitleNounProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('Anime'),
            UserLibraryKind::Manga => __('Manga'),
        };
    }

    /**
     * Returns the og:description and meta description for the active kind.
     *
     * @return string
     */
    public function getOgDescriptionProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('Browse the anime continuing this season. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]),
            UserLibraryKind::Manga => __('Browse the manga continuing this season. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]),
        };
    }

    /**
     * Returns the canonical URL for the active kind's continuing index.
     *
     * @return string
     */
    public function getCanonicalUrlProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => route('anime.continuing.index'),
            UserLibraryKind::Manga => route('manga.continuing.index'),
        };
    }

    /**
     * Returns the heading shown above the list.
     *
     * @return string
     */
    public function getHeadingProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('Continuing Anime'),
            UserLibraryKind::Manga => __('Continuing Manga'),
        };
    }

    /**
     * Returns the empty-state placeholder image filename.
     *
     * @return string
     */
    public function getEmptyImageProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => 'empty_anime_library.webp',
            UserLibraryKind::Manga => 'empty_manga_library.webp',
        };
    }

    /**
     * Returns the empty-state heading.
     *
     * @return string
     */
    public function getEmptyHeadingProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('No Continuing Anime'),
            UserLibraryKind::Manga => __('No Continuing Manga'),
        };
    }

    /**
     * Returns the empty-state body copy.
     *
     * @return string
     */
    public function getEmptyDescriptionProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('There are currently no ongoing anime.'),
            UserLibraryKind::Manga => __('There are currently no ongoing manga.'),
        };
    }

    /**
     * Returns the aria-label for the random-item dice button.
     *
     * @return string
     */
    public function getRandomLabelProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => 'random continuing anime',
            UserLibraryKind::Manga => 'random continuing manga',
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.browse.continuing.index');
    }
}
