<?php

namespace App\Http\Controllers\Web;

use App\Enums\SearchType;
use App\Http\Controllers\Controller;
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
use Illuminate\Http\Request;

class SearchSuggestionsController extends Controller
{
    /**
     * The search query.
     *
     * @var string $searchQuery
     */
    protected string $searchQuery = '';

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
     * Show the search suggestions for a query.
     *
     * @param Request $request
     *
     * @return Application|Factory|View
     * @throws InvalidEnumKeyException
     */
    public function index(Request $request): Application|Factory|View
    {
        $this->searchQuery = trim($request->string('q'));

        return $this->render();
    }

    /**
     * Render the suggestions.
     *
     * @return Application|Factory|View
     * @throws InvalidEnumKeyException
     */
    protected function render(): Application|Factory|View
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
                                $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
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
                                    'translation',
                                ])
                                    ->when(auth()->user(), function ($query, $user) {
                                        $query->withExists([
                                            'userWatchedEpisodes as isWatched' => function ($query) use ($user) {
                                                $query->where('user_id', $user->id)
                                                    ->completed();
                                            },
                                        ]);
                                    });
                                break;
                            case Person::class:
                            case Studio::class:
                                $query->with(['media']);
                                break;
                            case Song::class:
                                $query->with(['media', 'translations']);
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

        return view('search.suggestions', [
            'searchQuery' => $this->searchQuery,
            'searchResults' => $searchResults,
            'quickLinks' => static::quickLinks(),
        ]);
    }

    /**
     * The links offered before a query is entered.
     *
     * @return array
     */
    public static function quickLinks(): array
    {
        return [
            [
                'title' => __('Random Anime'),
                'link' => route('random.anime'),
            ],
            [
                'title' => __('Random Manga'),
                'link' => route('random.manga'),
            ],
            [
                'title' => __('Random Game'),
                'link' => route('random.games'),
            ],
            [
                'title' => __('About :x+', ['x' => config('app.name')]),
                'link' => route('kb.iap'),
            ],
            [
                'title' => __('About Personalization'),
                'link' => route('kb.personalization'),
            ],
            [
                'title' => __('Welcome to :x', ['x' => config('app.name')]),
                'link' => route('welcome'),
            ],
        ];
    }
}
