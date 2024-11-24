<?php

namespace App\Livewire;

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
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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
        Manga::class,
        Game::class,
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
     * Redirect the user to a random manga.
     *
     * @return void
     */
    public function randomManga(): void
    {
        $this->redirect(route('manga.details', Manga::inRandomOrder()->first()));
    }

    /**
     * Redirect the user to a random game.
     *
     * @return void
     */
    public function randomGame(): void
    {
        $this->redirect(route('games.details', Game::inRandomOrder()->first()));
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
                    ->query(function (Builder $query) use ($searchableModel) {
                        switch ($searchableModel) {
                            case Anime::class:
                            case Game::class:
                            case Manga::class:
                                $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
                                    ->when(auth()->user(), function ($query, $user) {
                                        $query->with(['library' => function ($query) use ($user) {
                                            $query->where('user_id', '=', $user->id);
                                        }]);
                                    });
                                break;
                            case Character::class:
                                $query->with(['media', 'translation']);
                                break;
                            case Episode::class:
                                $query->with([
                                    'anime' => function ($query) {
                                        $query->with(['media', 'translation']);
                                    },
                                    'media',
                                    'season' => function ($query) {
                                        $query->with(['translation']);
                                    },
                                    'translation'
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
                    })
                    ->take(5)
                    ->get();

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
                    'title'  => __('Random Manga'),
                    'action' => 'randomManga',
                ],
                [
                    'title'  => __('Random Game'),
                    'action' => 'randomGame',
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
