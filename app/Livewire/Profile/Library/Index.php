<?php

namespace App\Livewire\Profile\Library;

use App\Enums\UserLibraryKind;
use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaType;
use App\Models\User;
use App\Models\UserLibrary;
use App\Traits\Livewire\WithSearch;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Laravel\Scout\Builder as ScoutBuilder;
use Livewire\Component;

class Index extends Component
{
    use WithSearch {
        queryString as protected parentQueryString;
    }

    /**
     * The user whose library is being viewed.
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
     * The status of the library.
     *
     * @var string $status
     */
    public string $status = '';

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * The query strings of the component.
     *
     * @return array
     */
    protected function queryString(): array
    {
        $queryString = $this->parentQueryString();
        $queryString[] = 'status';
        return $queryString;
    }

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

        $status = str($this->status)->title();
        $status = str_replace('-', '', $status);

        if (!UserLibraryStatus::hasKey($status)) {
            $this->status = $this->defaultStatus();
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
     * Redirects the user to a random item in the current library list.
     *
     * @throws InvalidEnumKeyException
     * @throws InvalidEnumMemberException
     */
    public function randomItem(): void
    {
        if ($this->searchResults?->isEmpty() ?? true) {
            return;
        }

        $upperCaseStatus = implode('-', array_map('ucfirst', explode('-', $this->status)));
        $status = str_replace('-', '', $upperCaseStatus);
        $userLibraryStatus = UserLibraryStatus::fromKey($status);

        $item = $this->user
            ->whereTracked($this->modelClass())
            ->when(auth()->id() !== $this->user->id, function ($query) {
                $query->where(UserLibrary::TABLE_NAME . '.is_hidden', '=', false);
            })
            ->wherePivot('status', $userLibraryStatus->value)
            ->withoutIgnoreList()
            ->inRandomOrder()
            ->first();

        $this->redirectRoute($this->detailsRoute(), $item);
    }

    /**
     * Returns the paginated search results for the user's library.
     *
     * @throws InvalidEnumKeyException
     * @throws InvalidEnumMemberException
     */
    public function getSearchResultsProperty(): ?LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return null;
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

        $upperCaseStatus = implode('-', array_map('ucfirst', explode('-', $this->status)));
        $status = str_replace('-', '', $upperCaseStatus);
        $userLibraryStatus = UserLibraryStatus::fromKey($status);

        $hydrate = $this->hydrationCallback();
        $modelClass = $this->modelClass();

        if (empty($this->search) && empty($wheres) && empty($whereIns) && empty($orders)) {
            $models = $this->user
                ->whereTracked($modelClass)
                ->when(auth()->id() !== $this->user->id, function ($query) {
                    $query->where(UserLibrary::TABLE_NAME . '.is_hidden', '=', false);
                })
                ->withoutIgnoreList()
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
                })
                ->wherePivot('status', $userLibraryStatus->value);
            return $models->paginate($this->perPage);
        }

        return $this->paginateLibraryScopedSearch(
            modelClass: $modelClass,
            userId: $this->user->id,
            statuses: [$userLibraryStatus->value],
            excludeHidden: auth()->id() !== $this->user->id,
            wheres: $wheres,
            whereIns: $whereIns,
            orders: $orders,
            hydrate: $hydrate,
        );
    }

    /** Returns a closure that attaches the standard eager loads for trackable hydration. */
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

    /** Routes the trackable hydration through the shared eager-load callback. */
    public function searchQuery(ScoutBuilder $query): ScoutBuilder
    {
        return $query->query($this->hydrationCallback());
    }

    /** Strips the library status filter from the per-kind filterable attributes. */
    public function setFilterableAttributes(): array
    {
        $modelClass = $this->modelClass();
        $filterableAttributes = $modelClass::webSearchFilters();
        unset($filterableAttributes['library_status']);
        return $filterableAttributes;
    }

    /** Returns the per-kind orderable attributes. */
    public function setOrderableAttributes(): array
    {
        $modelClass = $this->modelClass();
        return $modelClass::webSearchOrders();
    }

    /** Returns the per-kind search type list keyed by media-type id. */
    public function setSearchTypes(): array
    {
        return MediaType::where('type', '=', $this->kindSlug())
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend(__('All'), 'all')
            ->toArray();
    }

    /** Returns the localized "Anime/Manga/Game Library" suffix used in the page title and og:title. */
    public function getTitleSuffixProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('Anime Library'),
            UserLibraryKind::Manga => __('Manga Library'),
            UserLibraryKind::Game  => __('Game Library'),
        };
    }

    /** Returns the per-kind status select array driving the tab strip. */
    public function getStatusSelectArrayProperty(): array
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => UserLibraryStatus::asAnimeSelectArray(),
            UserLibraryKind::Manga => UserLibraryStatus::asMangaSelectArray(),
            UserLibraryKind::Game  => UserLibraryStatus::asGameSelectArray(),
        };
    }

    /** Returns the per-kind empty-state placeholder image filename. */
    public function getEmptyImageProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => 'empty_anime_library.webp',
            UserLibraryKind::Manga => 'empty_manga_library.webp',
            UserLibraryKind::Game  => 'empty_game_library.webp',
        };
    }

    /** Returns the per-kind empty-state heading. */
    public function getEmptyHeadingProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('No Shows'),
            UserLibraryKind::Manga => __('No Manga'),
            UserLibraryKind::Game  => __('No Games'),
        };
    }

    /** Returns the per-kind empty-state body copy referencing the active status. */
    public function getEmptyDescriptionProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('Add a show to your :x list and it will show up here.', ['x' => strtolower($this->status)]),
            UserLibraryKind::Manga => __('Add a manga to your :x list and it will show up here.', ['x' => strtolower($this->status)]),
            UserLibraryKind::Game  => __('Add a game to your :x list and it will show up here.', ['x' => strtolower($this->status)]),
        };
    }

    /** Returns the per-kind aria-label for the random-item dice button. */
    public function getRandomLabelProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => 'random anime from ' . strtolower($this->status) . ' library',
            UserLibraryKind::Manga => 'random manga from ' . strtolower($this->status) . ' library',
            UserLibraryKind::Game  => 'random game from ' . strtolower($this->status) . ' library',
        };
    }

    /** Returns the model class for the active library kind. */
    protected function modelClass(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => Anime::class,
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game  => Game::class,
        };
    }

    /** Returns the default library status for the active library kind. */
    protected function defaultStatus(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => 'watching',
            UserLibraryKind::Manga => 'reading',
            UserLibraryKind::Game  => 'playing',
        };
    }

    /** Returns the route name for a single trackable's detail page. */
    protected function detailsRoute(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => 'anime.details',
            UserLibraryKind::Manga => 'manga.details',
            UserLibraryKind::Game  => 'games.details',
        };
    }

    /** Returns the media-type slug used to scope MediaType lookups. */
    protected function kindSlug(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => 'anime',
            UserLibraryKind::Manga => 'manga',
            UserLibraryKind::Game  => 'game',
        };
    }

    /** Renders the unified library view. */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.library.index');
    }
}
