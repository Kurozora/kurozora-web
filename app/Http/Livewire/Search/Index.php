<?php

namespace App\Http\Livewire\Search;

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
use App\Traits\Livewire\WithPagination;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

class Index extends Component
{
    use WithPagination;

    /**
     * The type of the search.
     *
     * @var string $type
     */
    public string $type = SearchType::Shows;

    /**
     * The scope of the search.
     *
     * @var string $scope
     */
    public string $scope = SearchScope::Kurozora;

    /**
     * The search query.
     *
     * @var string $q
     */
    public string $q = '';

    /**
     * The source of the search request.
     *
     * @var string $src
     */
    public string $src = SearchSource::Kurozora;

    /**
     * The filterable attributes.
     *
     * @var array $filter
     */
    public array $filter = [];

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * The query strings of the component.
     *
     * @var string[] $queryString
     */
    protected $queryString = [
        'scope' => ['except' => SearchScope::Kurozora],
        'type' => ['except' => SearchType::Shows],
        'q' => ['except' => ''],
        'perPage' => ['except' => 25],
        'src' => ['except' => SearchSource::Kurozora],
    ];

    /**
     * The rules of the component.
     *
     * @return string[][]
     */
    protected function rules(): array
    {
        return [
            'scope'     => ['nullable', 'string', 'in:' . implode(',', SearchScope::getValues())],
            'type'      => ['nullable', 'string', 'distinct', 'in:' . implode(',', SearchType::getWebValues($this->scope))],
            'q'         => ['string', 'min:1'],
            'perPage'   => ['nullable', 'integer', 'min:1', 'max:25'],
            'src'       => ['string', 'in:' . implode(',', SearchSource::getValues())],
        ];
    }

    /**
     * Prepare the component.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->setFilterableAttributes();
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
     * Called when a property is updated.
     *
     * @param $propertyName
     * @return void
     */
    public function updated($propertyName): void
    {
        if ($propertyName == 'scope' && !in_array($this->type, SearchType::getWebValues($this->scope))) {
            $this->type = SearchType::Shows;
        }
        if ($propertyName == 'type') {
            $this->setFilterableAttributes();
        }
        $this->validateOnly($propertyName);
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
            $this->validate();

            if (empty($this->q)) {
                return null;
            }

            $searchableModel = match ($this->type) {
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

            // Filter
            $wheres = [];

            foreach ($this->filter as $attribute => $filter) {
                $attribute = str_replace(':', '.', $attribute);
                $selected = $filter['selected'];
                $type = $filter['type'];

                if ((is_numeric($selected) && $selected >= 0) || !empty($selected)) {
                    $wheres[$attribute] = match ($type) {
                        'date' => Carbon::createFromFormat('Y-m-d', $selected)
                            ->setTime(0, 0)
                            ->timestamp,
                        'time' => $selected . ':00',
                        'double' => number_format($selected, 2, '.', ''),
                        default => $selected,
                    };
                }
            }

            // Search
            $models = $searchableModel::search($this->q)
                ->query(function ($query) use ($searchableModel) {
                    switch ($searchableModel) {
                        case Anime::class:
                        case Game::class:
                        case Manga::class:
                            $query->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating']);
                            break;
                        case Character::class:
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
                            ]);
                            break;
                        case Person::class:
                        case Studio::class:
                        case Song::class:
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

            $models->wheres = $wheres;

            if ($this->scope == SearchScope::Library) {
                $trackableIDs = collect(UserLibrary::search($this->q)
                    ->where('user_id', auth()->user()->id)
                    ->simplePaginateRaw(perPage: 2000, page: 1)
                    ->items()['hits'] ?? [])
                    ->pluck('trackable_id')
                    ->toArray();
                $models->whereIn('id', $trackableIDs);
            }

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
        return match ($this->type) {
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
     * Set the filterable attributes of the model.
     *
     * @return void
     */
    public function setFilterableAttributes(): void
    {
        $this->filter = match ($this->type) {
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
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.search.index');
    }
}
