<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\SearchScope;
use App\Enums\SearchType;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\EpisodeResourceIdentity;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\PersonResourceIdentity;
use App\Http\Resources\SongResourceIdentity;
use App\Http\Resources\StudioResourceIdentity;
use App\Http\Resources\UserResourceIdentity;
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
use App\Scopes\IgnoreListScope;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Laravel\Scout\Builder;
use Uri;

class SearchController extends Controller
{
    /**
     * Retrieves search results of the given type.
     *
     * @param SearchRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $data = $request->validated();
        $scope = $data['scope'];
        $types = $data['types'];

        if ($scope == SearchScope::Library && !auth()->check()) {
            throw new AuthenticationException('The request wasn’t accepted due to an issue with the credentials.');
        }

        $response = [];
        foreach ($types as $type) {
            switch ($type) {
                case SearchType::Characters:
                    $resource = Character::search($data['query'] ?? '');
                    $this->filter(Character::class, $request, $resource);
                    $resource = $resource->paginate($data['limit'] ?? 20)
                        ->appends($data);

                    // Get next page url minus domain
                    $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                    $response[$type] = [
                        'data' => CharacterResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Episodes:
                    $resource = Episode::search($data['query'] ?? '');
                    $this->filter(Episode::class, $request, $resource);
                    $resource = $resource->paginate($data['limit'] ?? 20)
                        ->appends($data);

                    // Get next page url minus domain
                    $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                    $response[$type] = [
                        'data' => EpisodeResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Games:
                    if ($scope == SearchScope::Library) {
                        $resource = UserLibrary::search($data['query'] ?? '')
                            ->where('user_id', auth()->id())
                            ->where('trackable_type', addslashes(Game::class))
                            ->query(function ($query) {
                                $query->with([
                                    'trackable' => function ($query) {
                                        $query->withoutGlobalScopes([IgnoreListScope::class]);
                                    },
                                    'user'
                                ]);
                            });
                        $this->filter(Game::class, $request, $resource);
                        $resource = $resource->paginate($data['limit'] ?? 5)
                            ->appends($data);

                        // Get next page url minus domain
                        $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                        $resource = collect($resource->items())->pluck('trackable');
                    } else {
                        $resource = Game::search($data['query'] ?? '');
                        $this->filter(Game::class, $request, $resource);
                        $resource = $resource->paginate($data['limit'] ?? 5)
                            ->appends($data);

                        // Get next page url minus domain
                        $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);
                    }

                    $response[$type] = [
                        'data' => GameResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Literatures:
                    if ($scope == SearchScope::Library) {
                        $resource = UserLibrary::search($data['query'] ?? '')
                            ->where('user_id', auth()->id())
                            ->where('trackable_type', addslashes(Manga::class))
                            ->query(function ($query) {
                                $query->with([
                                    'trackable' => function ($query) {
                                        $query->withoutGlobalScopes([IgnoreListScope::class]);
                                    },
                                    'user'
                                ]);
                            });
                        $this->filter(Manga::class, $request, $resource);
                        $resource = $resource->paginate($data['limit'] ?? 5)
                            ->appends($data);

                        // Get next page url minus domain
                        $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                        $resource = collect($resource->items())->pluck('trackable');
                    } else {
                        $resource = Manga::search($data['query'] ?? '');
                        $this->filter(Manga::class, $request, $resource);
                        $resource = $resource->paginate($data['limit'] ?? 5)
                            ->appends($data);

                        // Get next page url minus domain
                        $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);
                    }

                    $response[$type] = [
                        'data' => LiteratureResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::People:
                    $resource = Person::search($data['query'] ?? '');
                    $this->filter(Person::class, $request, $resource);
                    $resource = $resource->paginate($data['limit'] ?? 5)
                        ->appends($data);

                    // Get next page url minus domain
                    $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                    $response[$type] = [
                        'data' => PersonResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Shows:
                    if ($scope == SearchScope::Library) {
                        $resource = UserLibrary::search($data['query'] ?? '')
                            ->where('user_id', auth()->id())
                            ->where('trackable_type', addslashes(Anime::class))
                            ->query(function ($query) {
                                $query->with([
                                    'trackable' => function ($query) {
                                        $query->withoutGlobalScopes([IgnoreListScope::class]);
                                    },
                                    'user'
                                ]);
                            });
                        $this->filter(Anime::class, $request, $resource);
                        $resource = $resource->paginate($data['limit'] ?? 5)
                            ->appends($data);

                        // Get next page url minus domain
                        $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                        $resource = collect($resource->items())->pluck('trackable');
                    } else {
                        $resource = Anime::search($data['query'] ?? '');
                        $this->filter(Anime::class, $request, $resource);
                        $resource = $resource->simplePaginate($data['limit'] ?? 5)
                            ->appends($data);

                        // Get next page url minus domain
                        $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);
                    }

                    $response[$type] = [
                        'data' => AnimeResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Songs:
                    $resource = Song::search($data['query'] ?? '');
                    $this->filter(Song::class, $request, $resource);
                    $resource = $resource->paginate($data['limit'] ?? 5)
                        ->appends($data);

                    // Get next page url minus domain
                    $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                    $response[$type] = [
                        'data' => SongResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Studios:
                    $resource = Studio::search($data['query'] ?? '');
                    $this->filter(Studio::class, $request, $resource);
                    $resource = $resource->paginate($data['limit'] ?? 5)
                        ->appends($data);

                    // Get next page url minus domain
                    $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                    $response[$type] = [
                        'data' => StudioResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Users:
                    $resource = User::search($data['query'] ?? '');
                    $this->filter(User::class, $request, $resource);
                    $resource = $resource->paginate($data['limit'] ?? 5)
                        ->appends($data);

                    // Get next page url minus domain
                    $nextPageURL = $this->nextPageUrlFor($request, $resource, $type);

                    $response[$type] = [
                        'data' => UserResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                default:
                    break;
            }
        }

        return JSONResult::success([
            'data' => $response
        ]);
    }

    /**
     * Applies filter to the search request.
     *
     * @param               $model
     * @param SearchRequest $request
     * @param Builder       $resource
     *
     * @return void
     */
    private function filter($model, SearchRequest $request, Builder $resource)
    {
        if ($filters = $request->input('filter')) {
            $filters = json_decode(base64_decode($filters), true);
            $searchFilters = $model::searchFilters();

            // Apply filters
            $wheres = [];
            $whereIns = [];

            foreach ($searchFilters as $searchFilter) {
                if (isset($filters[$searchFilter])) {
                    $value = $filters[$searchFilter];

                    if (is_string($value) && str_contains($value, ',')) {
                        $values = array_map('trim', explode(',', $value));
                        $whereIns[$searchFilter] = $values;
                    } else {
                        $wheres[$searchFilter] = $value;
                    }
                }
            }

            $resource->wheres = $wheres;
            $resource->whereIns = $whereIns;
        }
    }

    /**
     * Returns a list of search suggestions.
     *
     * @param SearchRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function suggestions(SearchRequest $request): JsonResponse
    {
        $data = $request->validated();
        $scope = $data['scope'];
        $types = $data['types'];
        $query = $data['query'];

        if ($scope == SearchScope::Library && !auth()->check()) {
            throw new AuthenticationException('The request wasn’t accepted due to an issue with the credentials.');
        }

        $response = collect();
        foreach ($types as $type) {
            switch ($type) {
                case SearchType::Characters:
                    $resource = collect(Character::search($query)
                        ->take($data['limit'] ?? 5)
                        ->raw()['hits'])
                        ->map(function ($item) {
                            return $item['name'];
                        });
                    $response = $response->merge($resource);
                    break;
                case SearchType::Episodes:
                    $resource = collect(Episode::search($query)
                        ->take($data['limit'] ?? 5)
                        ->raw()['hits'])
                        ->map(function ($item) {
                            return $item['title'];
                        });
                    $response = $response->merge($resource);
                    break;
                case SearchType::Games:
                    if ($scope == SearchScope::Library) {
                        $resource = collect(UserLibrary::search($data['query'] ?? '')
                            ->where('user_id', auth()->id())
                            ->where('trackable_type', addslashes(Game::class))
                            ->take($data['limit'] ?? 5)
                            ->raw()['hits'])
                            ->map(function ($item) {
                                return $item['trackable']['title'];
                            })
                            ->toArray();
                    } else {
                        $resource = collect(Game::search($query)
                            ->take($data['limit'] ?? 5)
                            ->raw()['hits'])
                            ->map(function ($item) {
                                return $item['title'];
                            })
                            ->toArray();
                    }

                    $response = $response->merge($resource);
                    break;
                case SearchType::Literatures:
                    if ($scope == SearchScope::Library) {
                        $resource = collect(UserLibrary::search($data['query'] ?? '')
                            ->where('user_id', auth()->id())
                            ->where('trackable_type', addslashes(Manga::class))
                            ->take($data['limit'] ?? 5)
                            ->raw()['hits'])
                            ->map(function ($item) {
                                return $item['trackable']['title'];
                            })
                            ->toArray();
                    } else {
                        $resource = collect(Manga::search($query)
                            ->take($data['limit'] ?? 5)
                            ->raw()['hits'])
                            ->map(function ($item) {
                                return $item['title'];
                            })
                            ->toArray();
                    }

                    $response = $response->merge($resource);
                    break;
                case SearchType::People:
                    $resource = collect(Person::search($query)
                        ->take($data['limit'] ?? 5)
                        ->raw()['hits'])
                        ->map(function ($item) {
                            return $item['full_name'];
                        });
                    $response = $response->merge($resource);
                    break;
                case SearchType::Shows:
                    if ($scope == SearchScope::Library) {
                        $resource = collect(UserLibrary::search($query)
                            ->where('user_id', auth()->id())
                            ->where('trackable_type', addslashes(Anime::class))
                            ->take($data['limit'] ?? 5)
                            ->raw()['hits'])
                            ->map(function ($item) {
                                return $item['trackable']['title'];
                            })
                            ->toArray();
                    } else {
                        $resource = collect(Anime::search($query)
                            ->take($data['limit'] ?? 5)
                            ->raw()['hits'])
                            ->map(function ($item) {
                                return $item['title'];
                            });
                    }

                    $response = $response->merge($resource);
                    break;
                case SearchType::Songs:
                    $resource = collect(Song::search($query)
                        ->raw()['hits'])
                        ->map(function ($item) {
                            return $item['title'];
                        });
                    $response = $response->merge($resource);
                    break;
                case SearchType::Studios:
                    $resource = collect(Studio::search($query)
                        ->take($data['limit'] ?? 5)
                        ->raw()['hits'])
                        ->map(function ($item) {
                            return $item['name'];
                        });
                    $response = $response->merge($resource);
                    break;
                case SearchType::Users:
                    $resource = collect(User::search($query)
                        ->take($data['limit'] ?? 5)
                        ->raw()['hits'])
                        ->map(function ($item) {
                            return $item['username'];
                        });
                    $response = $response->merge($resource);
                    break;
                default:
                    break;
            }
        }

        $response = $response
            ->unique(function ($item) {
                return strtolower(trim($item));
            })
            ->sort(function ($a, $b) use ($query) {
                similar_text(strtolower(trim($a)), strtolower(trim($query)), $percentA);
                similar_text(strtolower(trim($b)), strtolower(trim($query)), $percentB);
                return $percentB <=> $percentA;
            })
            ->values();

        return JSONResult::success([
            'data' => $response
        ]);
    }

    /**
     * Generate the next page url for the given resource.
     *
     * @param SearchRequest                  $request
     * @param LengthAwarePaginator|Paginator $resource
     * @param string                         $type
     *
     * @return string|null
     */
    protected function nextPageUrlFor(SearchRequest $request, LengthAwarePaginator|Paginator $resource, string $type): ?string
    {
        $nexPageUrl = $resource->nextPageUrl();

        if (empty($nexPageUrl)) {
            return null;
        }

        $uri = Uri::of($nexPageUrl)
            ->withoutQuery(['types'])
            ->withQuery([
                'types' => [
                    $type
                ],
            ]);
        $path = $uri->path();
        $query = $uri->query()->value();
        return '/' . $path . '?' . $query;
    }
}
