<?php

namespace App\Http\Livewire;

use App\Enums\SearchType;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use App\Models\User;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class NavSearch extends Component
{
    /**
     * The array of searchable models.
     *
     * @var array|string[] $searchableModels
     */
    protected array $searchableModels = [
        Anime::class,
        Episode::class,
        Character::class,
        Person::class,
        Song::class,
        Studio::class,
        User::class,
    ];

    /**
     * The search query string.
     *
     * @var string $searchQuery
     */
    public string $searchQuery = '';

    /**
     * Redirect the user to a random anime.
     *
     * @return void
     */
    public function randomAnime(): void
    {
        $this->redirect(route('anime.details', Anime::inRandomOrder()->first()));
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     * @throws InvalidEnumKeyException
     */
    public function render(): Application|Factory|View
    {
        $searchResults = [];

        if (!empty($this->searchQuery)) {
            foreach ($this->searchableModels as $searchableModel) {
                $results = $searchableModel::search($this->searchQuery)
                    ->paginate(5);
                if ($results->count()) {
                    $result = [];
                    $result['title'] = str($searchableModel::TABLE_NAME)->title();
                    $result['type'] = $searchableModel::TABLE_NAME;
                    $result['search_type'] = SearchType::fromModel($searchableModel)->value;
                    $result['results'] = $results;
                    $searchResults[] = $result;
                }
            }
        }

        return view('livewire.nav-search', [
            'searchResults' => $searchResults,
            'quickLinks'    => [
                [
                    'title'  => __('Random Anime'),
                    'action' => 'randomAnime',
                ],
                [
                    'title' => __('About Kurozora+'),
                    'link'  => route('kb.iap'),
                ],
                [
                    'title' => __('About Personalisation'),
                    'link'  => route('kb.personalisation'),
                ],
                [
                    'title' => __('Welcome to Kurozora'),
                    'link'  => route('welcome'),
                ]
            ],
        ]);
    }
}
