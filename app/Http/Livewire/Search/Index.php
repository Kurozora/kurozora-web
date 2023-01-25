<?php

namespace App\Http\Livewire\Search;

use App\Enums\SearchScope;
use App\Enums\SearchSource;
use App\Enums\SearchType;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Manga;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use App\Models\User;
use App\Models\UserLibrary;
use App\Traits\Livewire\WithPagination;
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
     * The query strings of the component.
     *
     * @var string[] $queryString
     */
    protected $queryString = [
        'scope',
        'type',
        'q' => ['except' => ''],
        'perPage' => ['except' => 25],
        'src',
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
        $this->validateOnly($propertyName);
    }

    /**
     * The computed search results property.
     *
     * @return ?LengthAwarePaginator
     */
    public function getSearchResultsProperty(): ?LengthAwarePaginator
    {
        try {
            $this->validate();

            if (empty($this->q)) {
                return null;
            }

            $models = match ($this->type) {
                SearchType::Literature => Manga::class,
                SearchType::Episodes => Episode::class,
                SearchType::Characters => Character::class,
                SearchType::People => Person::class,
                SearchType::Songs => Song::class,
                SearchType::Studios => Studio::class,
                SearchType::Users => User::class,
                default => Anime::class,
            };

            $models = $models::search($this->q);
            if ($this->scope == SearchScope::Library) {
                $trackableIDs = UserLibrary::search($this->q)
                    ->where('user_id', auth()->user()->id)
                    ->take(2000)
                    ->get()
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
            SearchType::Literature => [
                'Blame',
                'Summertime Render',
                'アキラ',
                'Arachnid',
                'Bartender',
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
                'TheNaughtyOne',
            ],
            default => [],
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
