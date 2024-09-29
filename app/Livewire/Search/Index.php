<?php

namespace App\Livewire\Search;

use App\Enums\SearchScope;
use App\Enums\SearchSource;
use App\Enums\SearchType;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use App\Models\User;
use App\Models\UserLibrary;
use App\Traits\Livewire\WithSearch;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Laravel\Scout\Builder as ScoutBuilder;
use Livewire\Component;

class Index extends Component
{
    use WithSearch {
        queryString as protected parentQueryString;
        rules as protected parentRules;
    }

    /**
     * The internal type of the search.
     *
     * @var string $internalType
     */
    private string $internalType = SearchType::Shows;

    /**
     * The scope of the search.
     *
     * @var string $scope
     */
    public string $scope = SearchScope::Kurozora;

    /**
     * The source of the search request.
     *
     * @var string $src
     */
    public string $src = SearchSource::Kurozora;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * The query strings of the component.
     *
     * @return string[]
     */
    protected function queryString(): array
    {
        $queryString = $this->parentQueryString();
        $queryString['type'] = ['except' => 'anime'];
        $queryString['scope'] = ['except' => SearchScope::Kurozora];
        $queryString['src'] = ['except' => SearchSource::Kurozora];
        return $queryString;
    }

    /**
     * The rules of the component.
     *
     * @return string[][]
     */
    protected function rules(): array
    {
        $rules = $this->parentRules();
        $rules['scope'] = ['nullable', 'string', 'in:' . implode(',', SearchScope::getValues())];
        $rules['src'] = ['string', 'in:' . implode(',', SearchSource::getKeys())];
        return $rules;
    }

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
        if ($this->type === 'all') {
            $this->type = 'anime';
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
     * Called when the `scope` property is updated.
     *
     * @param string $newValue
     *
     * @return void
     */
    public function updatedScope(string $newValue): void
    {
        // Can't scope on library if not signed in.
        if ($newValue == SearchScope::Library && !auth()->check()) {
            $this->redirectRoute('sign-in');
            return;
        }

        // Update internal type if selected type and scope are compatible.
        if (
            $newValue !== SearchScope::Library &&
            in_array($this->internalType, SearchType::getWebValues($newValue))
        ) {
            $this->setInternalType($this->type);
            return;
        }

        // Update type to one compatible with the selected scope.
        $this->type = 'anime';
        $this->setInternalType($this->type);
    }

    /**
     * Called when the `type` property is updated.
     *
     * @param string $newValue
     *
     * @return void
     */
    public function updatedType(string $newValue): void
    {
        $this->typeValue = collect($this->searchTypes)
            ->search(function ($value) use ($newValue) {
                return str($value)->slug()->value() === $this->type;
            });

        $this->setInternalType($newValue);
    }

    /**
     * Set the internal type property based on the given type.
     *
     * @param $type
     *
     * @return void
     */
    private function setInternalType($type): void
    {
        $this->internalType = match ($type) {
            'anime' => SearchType::Shows,
            'games' => SearchType::Games,
            'manga' => SearchType::Literatures,
            default => $this->type
        };

        // Update other properties
        $this->order = $this->setOrderableAttributes();
        $this->filter = $this->setFilterableAttributes();
        $this->searchTypes = $this->setSearchTypes();
    }

    /**
     * The computed search results property.
     *
     * @return ?LengthAwarePaginator
     */
    public function getSearchResultsProperty(): ?LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return null;
        }

        try {
            $searchableModel = match ($this->internalType) {
                SearchType::Literatures => Manga::class,
                SearchType::Games => Game::class,
                SearchType::Episodes => Episode::class,
                SearchType::Characters => Character::class,
                SearchType::People => Person::class,
                SearchType::Songs => Song::class,
                SearchType::Studios => Studio::class,
                SearchType::Users => User::class,
                default => Anime::class,
            };

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

            // If no search, filter or order was performed, return nothing
            if (empty($this->search) && empty($wheres) && empty($whereIns) && empty($this->letter)) {
                return null;
            }

            // Get library status
            $userLibraryStatuses = $this->filter['library_status']['selected'] ?? null;
            $user = auth()->user();

            // Search
            if ($userLibraryStatuses) {
                $modelIDs = collect(UserLibrary::search($this->search)
                    ->when(!empty($this->letter), function (ScoutBuilder $query) {
                        $query->where('trackable.letter', $this->letter);
                    })
                    ->where('user_id', $user->id)
                    ->where('trackable_type', addslashes($searchableModel))
                    ->whereIn('status', $userLibraryStatuses)
                    ->simplePaginateRaw(perPage: 2000, page: 1)
                    ->items()['hits'] ?? [])
                    ->pluck('trackable_id')
                    ->toArray();
                $whereIns['id'] = $modelIDs;
            }

            $models = $searchableModel::search($this->search)
                ->query(function ($query) use ($searchableModel) {
                    switch ($searchableModel) {
                        case Anime::class:
                        case Game::class:
                        case Manga::class:
                            $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
                                ->when(auth()->user(), function ($query, $user) {
                                    $query->with(['library' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    }]);
                                });
                            break;
                        case Character::class:
                        case Song::class:
                            $query->with(['media', 'translations']);
                            break;
                        case Episode::class:
                            $query->with([
                                'anime' => function ($query) {
                                    $query->with(['media', 'translations']);
                                },
                                'media',
                                'season' => function ($query) {
                                    $query->with(['translations']);
                                },
                                'translations'
                            ])
                                ->when(auth()->user(), function ($query, $user) {
                                    $query->withExists([
                                        'user_watched_episodes as isWatched' => function ($query) use ($user) {
                                            $query->where('user_id', $user->id);
                                        }
                                    ]);
                                });
                            break;
                        case Person::class:
                        case Studio::class:
                            $query->with(['media']);
                            break;
                        case User::class:
                            $query->with(['media'])
                                ->withCount(['followers'])
                                ->when(auth()->user(), function ($query, $user) {
                                    $query->withExists(['followers as isFollowed' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    }]);
                                });
                            break;
                    }
                });

            // Search
            if ($this->scope == SearchScope::Library) {
                $modelIDs = collect(UserLibrary::search($this->search)
                    ->when(!empty($this->letter), function (ScoutBuilder $query) {
                        $query->where('trackable.letter', $this->letter);
                    })
                    ->when($userLibraryStatuses, function (ScoutBuilder $query) use ($userLibraryStatuses) {
                        $query->whereIn('status', $userLibraryStatuses);
                    })
                    ->where('user_id', $user->id)
                    ->where('trackable_type', addslashes($searchableModel))
                    ->simplePaginateRaw(perPage: 2000, page: 1)
                    ->items()['hits'] ?? [])
                    ->pluck('trackable_id')
                    ->toArray();
                $whereIns['id'] = $modelIDs;
            } else if (!empty($this->letter)) {
                $wheres['letter'] = $this->letter;
            }

            $models->orders = $orders;
            $models->wheres = $wheres;
            $models->whereIns = $whereIns;

            return $models->paginate($this->perPage);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * The computed search suggestions property.
     *
     * @return array|string[]
     */
    public function getSearchSuggestionsProperty(): array
    {
        return match ($this->internalType) {
            SearchType::Shows => [
                'One Piece',
                'Pokemon',
                'Re:Zero',
                'Death Note',
                'アキラ',
            ],
            SearchType::Literatures => [
                'Blame',
                'Summertime Render',
                'アキラ',
                'Arachnid',
                'Bartender',
            ],
            SearchType::Games => [
                'ワンピース オデッセイ',
                'Steins;Gate',
                'Danganronpa',
                'Pokémon Shining Pearl',
                'Lost in Memories',
            ],
            SearchType::Episodes => [
                'Zombie',
                'Red Hat',
                'Witch',
                'Subaru',
                'Cream Puff',
            ],
            SearchType::Characters => [
                'Kirito',
                'Subaru',
                'Issei',
                'Koro-sensei',
                'Izuku Midoriya',
            ],
            SearchType::People => [
                'Reki Kwahara',
                'Gosho Aoyama',
                'Mayumi Tanaka',
                '長月',
                'Hayao Miyazaki',
            ],
            SearchType::Songs => [
                'We are',
                'Strike It Out',
                'The Rumbling',
                'Wo Ai Ni',
                '爆走夢歌',
            ],
            SearchType::Studios => [
                'White Fox',
                'Rooster Teeth',
                'NTT Plala',
                'OLM',
                'Fuji TV',
            ],
            SearchType::Users => [
                'Kirito',
                'Usopp',
                'Kuro-chan',
                'Fejrix',
            ],
            default => [],
        };
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return array
     */
    public function setOrderableAttributes(): array
    {
        return match ($this->internalType) {
            SearchType::Shows => Anime::webSearchOrders(),
            SearchType::Literatures => Manga::webSearchOrders(),
            SearchType::Games => Game::webSearchOrders(),
            SearchType::Episodes => Episode::webSearchOrders(),
            SearchType::Characters => Character::webSearchOrders(),
            SearchType::People => Person::webSearchOrders(),
            SearchType::Songs => Song::webSearchOrders(),
            SearchType::Studios => Studio::webSearchOrders(),
            SearchType::Users => User::webSearchOrders(),
            default => []
        };
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return array
     */
    public function setFilterableAttributes(): array
    {
        return match ($this->internalType) {
            SearchType::Shows => Anime::webSearchFilters(),
            SearchType::Literatures => Manga::webSearchFilters(),
            SearchType::Games => Game::webSearchFilters(),
            SearchType::Episodes => Episode::webSearchFilters(),
            SearchType::Characters => Character::webSearchFilters(),
            SearchType::People => Person::webSearchFilters(),
            SearchType::Songs => Song::webSearchFilters(),
            SearchType::Studios => Studio::webSearchFilters(),
            SearchType::Users => User::webSearchFilters(),
            default => []
        };
    }

    /**
     * Set the search types of the model.
     *
     * @return array
     */
    public function setSearchTypes(): array
    {
        return SearchType::asWebSelectArray($this->scope);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.search.index');
    }
}
