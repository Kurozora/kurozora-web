<?php

namespace App\Livewire\Profile\Library;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaType;
use App\Models\User;
use App\Models\UserLibrary;
use App\Traits\Livewire\WithSearch;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;
use Livewire\Component;

class Reminders extends Component
{
    use WithSearch;

    /**
     * The user whose reminders are being viewed.
     *
     * @var User $user
     */
    public User $user;

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
     * @param User $user
     * @param int  $kind
     *
     * @return void
     */
    public function mount(User $user, int $kind): void
    {
        $this->user = $user;
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
     * Redirects the user to a random reminded item.
     *
     * @return void
     */
    public function randomItem(): void
    {
        if ($this->searchResults?->isEmpty() ?? true) {
            return;
        }

        $modelClass = $this->modelClass();

        $item = $this->user
            ->whereReminded($modelClass)
            ->when(auth()->id() !== $this->user->id, function ($query) use ($modelClass) {
                $query->whereExists(function ($subQuery) use ($modelClass) {
                    $subQuery->from(UserLibrary::TABLE_NAME)
                        ->whereColumn(UserLibrary::TABLE_NAME . '.trackable_id', $modelClass::TABLE_NAME . '.id')
                        ->where(UserLibrary::TABLE_NAME . '.trackable_type', '=', $modelClass)
                        ->where(UserLibrary::TABLE_NAME . '.user_id', '=', $this->user->id)
                        ->where(UserLibrary::TABLE_NAME . '.is_hidden', '=', false);
                });
            })
            ->inRandomOrder()
            ->first();

        match ($this->kind) {
            UserLibraryKind::Anime => $this->redirectRoute('anime.details', $item),
            UserLibraryKind::Manga => $this->redirectRoute('manga.details', $item),
            UserLibraryKind::Game  => $this->redirectRoute('games.details', $item),
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
        $userLibraryStatuses = $this->filter['library_status']['selected'] ?? null;
        $modelClass = $this->modelClass();

        if (empty($this->search) && empty($wheres) && empty($whereIns) && empty($orders)) {
            $models = $this->scopedQuery($modelClass)
                ->when($userLibraryStatuses, function ($query) use ($modelClass, $userLibraryStatuses) {
                    $query->join(UserLibrary::TABLE_NAME, function ($query) use ($modelClass, $userLibraryStatuses) {
                        $query->on(UserLibrary::TABLE_NAME . '.trackable_id', '=', $modelClass::TABLE_NAME . '.id')
                            ->where(UserLibrary::TABLE_NAME . '.user_id', '=', $this->user->id)
                            ->where(UserLibrary::TABLE_NAME . '.trackable_type', '=', $modelClass)
                            ->whereIn(UserLibrary::TABLE_NAME . '.status', $userLibraryStatuses);
                    });
                })
                ->when(auth()->id() !== $this->user->id, function ($query) use ($modelClass) {
                    $query->whereExists(function ($subQuery) use ($modelClass) {
                        $subQuery->from(UserLibrary::TABLE_NAME)
                            ->whereColumn(UserLibrary::TABLE_NAME . '.trackable_id', $modelClass::TABLE_NAME . '.id')
                            ->where(UserLibrary::TABLE_NAME . '.trackable_type', '=', $modelClass)
                            ->where(UserLibrary::TABLE_NAME . '.user_id', '=', $this->user->id)
                            ->where(UserLibrary::TABLE_NAME . '.is_hidden', '=', false);
                    });
                })
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
                    if ($this->letter == '.') {
                        $query->whereRaw($this->letterIndexColumn() . ' REGEXP \'^[^a-zA-Z]*$\'');
                    } else {
                        $query->whereLike($this->letterIndexColumn(), $this->letter . '%');
                    }
                });

            return $models->paginate($this->perPage);
        }

        $modelIDs = $this->scopedQuery($modelClass)
            ->when($userLibraryStatuses, function ($query) use ($modelClass, $userLibraryStatuses) {
                $query->join(UserLibrary::TABLE_NAME, function ($query) use ($modelClass, $userLibraryStatuses) {
                    $query->on(UserLibrary::TABLE_NAME . '.trackable_id', '=', $modelClass::TABLE_NAME . '.id')
                        ->where(UserLibrary::TABLE_NAME . '.user_id', '=', $this->user->id)
                        ->where(UserLibrary::TABLE_NAME . '.trackable_type', '=', $modelClass)
                        ->whereIn(UserLibrary::TABLE_NAME . '.status', $userLibraryStatuses);
                });
            })
            ->when(auth()->id() !== $this->user->id, function ($query) use ($modelClass) {
                $query->whereExists(function ($subQuery) use ($modelClass) {
                    $subQuery->from(UserLibrary::TABLE_NAME)
                        ->whereColumn(UserLibrary::TABLE_NAME . '.trackable_id', $modelClass::TABLE_NAME . '.id')
                        ->where(UserLibrary::TABLE_NAME . '.trackable_type', '=', $modelClass)
                        ->where(UserLibrary::TABLE_NAME . '.user_id', '=', $this->user->id)
                        ->where(UserLibrary::TABLE_NAME . '.is_hidden', '=', false);
                });
            })
            ->limit(2000)
            ->pluck($this->pivotIdColumn())
            ->toArray();
        $whereIns['id'] = $modelIDs;

        if (!empty($this->letter)) {
            $wheres['letter'] = $this->letter;
        }

        if (!empty($this->typeValue)) {
            $wheres[$this->typeColumn()] = $this->typeValue;
        }

        $models = $modelClass::search($this->search);
        $models->wheres = $wheres;
        $models->whereIns = $whereIns;
        $models->orders = $orders;
        $models = $this->searchQuery($models);

        return $models->paginate($this->perPage);
    }

    /**
     * Builds the base query for the active library kind.
     *
     * @param string $modelClass
     *
     * @return EloquentBuilder
     */
    protected function scopedQuery(string $modelClass): EloquentBuilder
    {
        return $this->user->whereReminded($modelClass)->withoutIgnoreList();
    }

    /**
     * Returns the foreign-key column on the reminders pivot table.
     *
     * @return string
     */
    protected function pivotIdColumn(): string
    {
        return 'remindable_id';
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
            UserLibraryKind::Game  => Game::class,
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
            UserLibraryKind::Game  => 'game',
        };
    }

    /**
     * Returns the localized page title for the current kind.
     *
     * @return string
     */
    public function getTitleProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('Anime Reminders'),
            UserLibraryKind::Manga => __('Manga Reminders'),
            UserLibraryKind::Game  => __('Game Reminders'),
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
            UserLibraryKind::Anime => 'random anime',
            UserLibraryKind::Manga => 'random manga',
            UserLibraryKind::Game  => 'random game',
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
            UserLibraryKind::Game  => 'empty_game_library.webp',
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
            UserLibraryKind::Anime => __('No Reminded Anime'),
            UserLibraryKind::Manga => __('No Reminded Mangas'),
            UserLibraryKind::Game  => __('No Reminded Games'),
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
            UserLibraryKind::Anime => __('Add an anime to reminders and it will show up here.'),
            UserLibraryKind::Manga => __('Add a manga to reminders and it will show up here.'),
            UserLibraryKind::Game  => __('Add a game to reminders and it will show up here.'),
        };
    }

    /**
     * Renders the unified reminders view.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.library.reminders');
    }
}
