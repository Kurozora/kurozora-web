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
use App\Http\Resources\GameResource;
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
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Retrieves search results of the given type.
     *
     * @param SearchRequest $request
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
                    $resource = Character::search($data['query'])
                        ->paginate($data['limit'] ?? 20)
                        ->appends($data);
                    // Get next page url minus domain
                    $nextPageURL = $this->nextPageUrlFor($resource, $type);

                    $response[$type] = [
                        'data' => CharacterResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Episodes:
                    $resource = Episode::search($data['query'])
                        ->paginate($data['limit'] ?? 20)
                        ->appends($data);
                    // Get next page url minus domain
                    $nextPageURL = $this->nextPageUrlFor($resource, $type);

                    $response[$type] = [
                        'data' => EpisodeResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Games:
                    if ($scope == SearchScope::Library) {
                        $resource = UserLibrary::search($data['query'])
                            ->where('user_id', auth()->id())
                            ->where('trackable_type', Game::class)
                            ->query(function ($query) {
                                $query->with(['trackable', 'user']);
                            })
                            ->paginate($data['limit'] ?? 5)
                            ->appends($data);
                        // Get next page url minus domain
                        $nextPageURL = $this->nextPageUrlFor($resource, $type);

                        $resource = collect($resource->items())->pluck('trackable');
                    } else {
                        $resource = Game::search($data['query']);
                        $resource = $resource->paginate($data['limit'] ?? 5)
                            ->appends($data);
                        // Get next page url minus domain
                        $nextPageURL = $this->nextPageUrlFor($resource, $type);
                    }

                    $response[$type] = [
                        'data' => GameResource::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Literatures:
                    if ($scope == SearchScope::Library) {
                        $resource = UserLibrary::search($data['query'])
                            ->where('user_id', auth()->id())
                            ->where('trackable_type', Manga::class)
                            ->query(function ($query) {
                                $query->with(['trackable', 'user']);
                            })
                            ->paginate($data['limit'] ?? 5)
                            ->appends($data);
                        // Get next page url minus domain
                        $nextPageURL = $this->nextPageUrlFor($resource, $type);

                        $resource = collect($resource->items())->pluck('trackable');
                    } else {
                        $resource = Manga::search($data['query']);
                        $resource = $resource->paginate($data['limit'] ?? 5)
                            ->appends($data);
                        // Get next page url minus domain
                        $nextPageURL = $this->nextPageUrlFor($resource, $type);
                    }

                    $response[$type] = [
                        'data' => LiteratureResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::People:
                    $resource = Person::search($data['query'])
                        ->paginate($data['limit'] ?? 5)
                        ->appends($data);
                    // Get next page url minus domain
                    $nextPageURL = $this->nextPageUrlFor($resource, $type);

                    $response[$type] = [
                        'data' => PersonResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Shows:
                    if ($scope == SearchScope::Library) {
                        $resource = UserLibrary::search($data['query'])
                            ->where('user_id', auth()->id())
                            ->where('trackable_type', Anime::class)
                            ->query(function ($query) {
                                $query->with(['trackable', 'user']);
                            })
                            ->paginate($data['limit'] ?? 5)
                            ->appends($data);
                        // Get next page url minus domain
                        $nextPageURL = $this->nextPageUrlFor($resource, $type);

                        $resource = collect($resource->items())->pluck('trackable');
                    } else {
                        $resource = Anime::search($data['query']);
                        $resource = $resource->paginate($data['limit'] ?? 5)
                            ->appends($data);
                        // Get next page url minus domain
                        $nextPageURL = $this->nextPageUrlFor($resource, $type);
                    }

                    $response[$type] = [
                        'data' => AnimeResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Songs:
                    $resource = Song::search($data['query'])
                        ->paginate($data['limit'] ?? 5)
                        ->appends($data);
                    // Get next page url minus domain
                    $nextPageURL = $this->nextPageUrlFor($resource, $type);

                    $response[$type] = [
                        'data' => SongResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Studios:
                    $resource = Studio::search($data['query'])
                        ->paginate($data['limit'] ?? 5)
                        ->appends($data);
                    // Get next page url minus domain
                    $nextPageURL = $this->nextPageUrlFor($resource, $type);

                    $response[$type] = [
                        'data' => StudioResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                case SearchType::Users:
                    $resource = User::search($data['query'])
                        ->paginate($data['limit'] ?? 5)
                        ->appends($data);
                    // Get next page url minus domain
                    $nextPageURL = $this->nextPageUrlFor($resource, $type);

                    $response[$type] = [
                        'data' => UserResourceIdentity::collection($resource),
                        'next' => empty($nextPageURL) ? null : $nextPageURL
                    ];
                    break;
                default: break;
            }
        }

        return JSONResult::success([
            'data' => $response
        ]);
    }

    /**
     * Returns a list of search suggestions.
     *
     * @param SearchRequest $request
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
                        $resource = collect(UserLibrary::search($data['query'])
                            ->where('user_id', auth()->id())
                            ->where('trackable_type', Game::class)
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
                        $resource = collect(UserLibrary::search($data['query'])
                            ->where('user_id', auth()->id())
                            ->where('trackable_type', Manga::class)
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
                            ->where('trackable_type', Anime::class)
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
                default: break;
            }
        }

        $response = $response
            ->unique(function($item) {
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
     * @param $resource
     * @param $type
     * @return string|null
     */
    protected function nextPageUrlFor($resource, $type): ?string
    {
        $nexPageUrl = $resource->nextPageUrl();

        if ($nexPageUrl) {
            $resourceUrl = parse_url($resource->nextPageUrl(), PHP_URL_QUERY);
            parse_str($resourceUrl, $queries);
            $queries['types'] = [$type];
            return route('api.search.index', $queries, false);
        }

        return null;
    }
}
