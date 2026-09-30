<?php

namespace App\Http\Controllers\Web;

use App\Enums\SearchScope;
use App\Enums\SearchSource;
use App\Enums\SearchType;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetSearchIndexRequest;
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
use App\Support\SearchCriteria;
use App\Support\SearchIndex;
use App\Traits\Controller\PaginatesTitles;
use Closure;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Laravel\Scout\Builder as ScoutBuilder;

class SearchController extends Controller
{
    use PaginatesTitles;

    /**
     * Show the search page.
     *
     * @param GetSearchIndexRequest $request
     *
     * @return Application|Factory|View|RedirectResponse
     */
    public function index(GetSearchIndexRequest $request): Application|Factory|View|RedirectResponse
    {
        $user = $request->user();
        $scope = $request->string('scope')->value() === SearchScope::Library
            ? SearchScope::Library
            : SearchScope::Kurozora;

        if ($scope === SearchScope::Library && $user === null) {
            return to_route('sign-in');
        }

        $searchTypes = SearchType::asWebSelectArray($scope);
        $searchType = SearchCriteria::fromRequest($request, searchTypes: $searchTypes, defaultType: 'anime')->typeValue
            ?? SearchType::Shows;
        $modelClass = $this->modelClass($searchType);

        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: $modelClass::webSearchFilters(),
            orders: $modelClass::webSearchOrders(),
            searchTypes: $searchTypes,
            defaultType: 'anime',
        );

        return view('search.index', [
            'criteria' => $criteria,
            'scope' => $scope,
            'searchType' => $searchType,
            'results' => $this->results($criteria, $modelClass, $scope, $user)
                ?->withQueryString(),
            'suggestions' => $this->suggestions($searchType),
            'schema' => [
                '@type' => 'WebSite',
                'url' => config('app.url'),
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => route('search.index') . '?q={search_term_string}&src=' . SearchSource::Google,
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ]);
    }

    /**
     * The paginated results of the criteria.
     *
     * @param SearchCriteria $criteria
     * @param string         $modelClass
     * @param string         $scope
     * @param User|null      $user
     *
     * @return LengthAwarePaginator|null
     */
    protected function results(SearchCriteria $criteria, string $modelClass, string $scope, ?User $user): ?LengthAwarePaginator
    {
        [$wheres, $whereIns] = $criteria->wheres();

        if ($criteria->search === '' && empty($wheres) && empty($whereIns) && $criteria->letter === '') {
            return null;
        }

        $libraryStatuses = $criteria->libraryStatuses();
        $searchesLibrary = $user !== null && ($scope === SearchScope::Library || !empty($libraryStatuses));

        try {
            $searchIndex = (new SearchIndex($modelClass, $criteria))
                ->withoutIndex()
                ->type(null)
                ->hydrate($this->hydration($modelClass));

            if ($searchesLibrary) {
                $libraryIds = $this->libraryIds($criteria, $modelClass, $user, $libraryStatuses);

                $searchIndex->search(function (ScoutBuilder $query) use ($libraryIds) {
                    unset($query->wheres['letter']);
                    $query->whereIn('id', $libraryIds);
                });
            }

            return $searchIndex->paginate();
        } catch (Exception) {
            return null;
        }
    }

    /**
     * The ids of the user's library entries the criteria match.
     *
     * @param SearchCriteria $criteria
     * @param string         $modelClass
     * @param User           $user
     * @param array          $libraryStatuses
     *
     * @return array
     */
    protected function libraryIds(SearchCriteria $criteria, string $modelClass, User $user, array $libraryStatuses): array
    {
        return collect(UserLibrary::search($criteria->search)
            ->when($criteria->letter !== '', function (ScoutBuilder $query) use ($criteria) {
                $query->where('trackable.letter', $criteria->letter);
            })
            ->when(!empty($libraryStatuses), function (ScoutBuilder $query) use ($libraryStatuses) {
                $query->whereIn('status', $libraryStatuses);
            })
            ->where('user_id', $user->id)
            ->where('trackable_type', addslashes($modelClass))
            ->simplePaginateRaw(perPage: 2000, page: 1)
            ->items()['hits'] ?? [])
            ->pluck('trackable_id')
            ->toArray();
    }

    /**
     * The eager loads the lockups of a searchable model need.
     *
     * @param string $modelClass
     *
     * @return Closure
     */
    protected function hydration(string $modelClass): Closure
    {
        return match ($modelClass) {
            Anime::class, Game::class, Manga::class => fn (Builder $query) => $this->hydrateTitles($query),
            Character::class, Song::class => fn (Builder $query) => $query->with(['media', 'translation']),
            Episode::class => fn (Builder $query) => $query->with([
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
                }),
            User::class => fn (Builder $query) => $query->with(['media'])
                ->withCount(['followers'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->withExists(['followers as isFollowed' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                }),
            default => fn (Builder $query) => $query->with(['media']),
        };
    }

    /**
     * The searchable model of a search type.
     *
     * @param string $searchType
     *
     * @return string
     */
    protected function modelClass(string $searchType): string
    {
        return match ($searchType) {
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
    }

    /**
     * The search suggestions of a search type.
     *
     * @param string $searchType
     *
     * @return array
     */
    protected function suggestions(string $searchType): array
    {
        return match ($searchType) {
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
}
