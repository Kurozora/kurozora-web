<?php

namespace App\Livewire\Profile\Library\Game;

use App\Models\Episode;
use App\Models\Game;
use App\Models\User;
use App\Models\UserLibrary;
use App\Traits\Livewire\WithGameSearch;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;
use Livewire\Component;

class Favorites extends Component
{
    use WithGameSearch;

    /**
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

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
    }

    /**
     * Redirect the user to a random model.
     *
     * @return void
     */
    public function randomGame(): void
    {
        $game = $this->user
            ->whereFavorited(Game::class)
            ->inRandomOrder()
            ->first();
        $this->redirectRoute('games.details', $game);
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

        // If no search was performed, return all games
        if (empty($this->search) && (empty($wheres) && empty($whereIns)) && empty($orders)) {
            $models = $this->user
                ->whereFavorited(static::$searchModel)
                ->withoutIgnoreList()
                ->when($userLibraryStatuses, function ($query) use ($userLibraryStatuses) {
                    $query->join(UserLibrary::TABLE_NAME, function ($query) use ($userLibraryStatuses) {
                        $query->on(UserLibrary::TABLE_NAME.'.trackable_id', '=', static::$searchModel::TABLE_NAME . '.id')
                            ->where(UserLibrary::TABLE_NAME.'.user_id', '=', $this->user->id)
                            ->where(UserLibrary::TABLE_NAME.'.trackable_type', '=', static::$searchModel)
                            ->whereIn(UserLibrary::TABLE_NAME.'.status', $userLibraryStatuses);
                    });
                })
                ->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->with(['library' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                });

            $models = $models
                ->when(!empty($this->typeValue), function (EloquentBuilder $query) {
                    $query->where($this->typeColumn(), '=', $this->typeValue);
                })
                ->when(!empty($this->letter), function (EloquentBuilder $query) {
                    if (static::$searchModel === Episode::class) {
                        $query->whereRelation('translations', function ($query) {
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
                });

            return $models->paginate($this->perPage);
        }

        $modelIDs = $this->user
            ->whereFavorited(static::$searchModel)
            ->withoutIgnoreList()
            ->when($userLibraryStatuses, function ($query) use ($userLibraryStatuses) {
                $query->join(UserLibrary::TABLE_NAME, function ($query) use ($userLibraryStatuses) {
                    $query->on(UserLibrary::TABLE_NAME.'.trackable_id', '=', static::$searchModel::TABLE_NAME . '.id')
                        ->where(UserLibrary::TABLE_NAME.'.user_id', '=', $this->user->id)
                        ->where(UserLibrary::TABLE_NAME.'.trackable_type', '=', static::$searchModel)
                        ->whereIn(UserLibrary::TABLE_NAME.'.status', $userLibraryStatuses);
                });
            })
            ->limit(2000)
            ->pluck('favorable_id')
            ->toArray();
        $whereIns['id'] = $modelIDs;

        if (!empty($this->letter)) {
            $wheres['letter'] = $this->letter;
        }

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
     * Build a 'search index' query for the given resource.
     *
     * @param EloquentBuilder $query
     *
     * @return EloquentBuilder
     */
    public function searchIndexQuery(EloquentBuilder $query): EloquentBuilder
    {
        return $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });
    }

    /**
     * Build a 'search' query for the given resource.
     *
     * @param ScoutBuilder $query
     * @return ScoutBuilder
     */
    public function searchQuery(ScoutBuilder $query): ScoutBuilder
    {
        return $query->query(function (EloquentBuilder $query) {
            $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });
        });
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.library.game.favorites');
    }
}
