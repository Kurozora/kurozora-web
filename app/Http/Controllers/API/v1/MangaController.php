<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\BrowseSeasonKind;
use App\Enums\SearchScope;
use App\Enums\SearchType;
use App\Events\ModelViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetBrowseSeasonRequest;
use App\Http\Requests\GetIndexRequest;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Requests\RateModelRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\LiteratureCastResourceIdentity;
use App\Http\Resources\LiteratureResource;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\MediaRatingResource;
use App\Http\Resources\MediaRelatedResource;
use App\Http\Resources\MediaStaffResource;
use App\Http\Resources\StudioResource;
use App\Models\Manga;
use App\Models\MediaRelation;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Routing\Redirector;

class MangaController extends Controller
{
    /**
     * Returns the manga index.
     *
     * @param GetIndexRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws BindingResolutionException
     */
    public function index(GetIndexRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['ids'])) {
            return $this->views($request);
        } else {
            // Override parameters
            $request->merge([
                'scope' => SearchScope::Kurozora,
                'types' => [
                    SearchType::Literatures
                ]
            ]);
            // Convert request type
            $app = app();
            $searchRequest = SearchRequest::createFrom($request)
                ->setContainer($app) // Necessary or validation fails (validate on null)
                ->setRedirector($app->make(Redirector::class)); // Necessary or validation failure fails (422)
            $searchRequest->validateResolved(); // Necessary for preparing for validation

            return (new SearchController())
                ->index($searchRequest);
        }
    }

    /**
     * Returns detailed information of a Manga.
     *
     * @param Request $request
     * @param Manga   $manga
     *
     * @return JsonResponse
     */
    public function view(Request $request, Manga $manga): JsonResponse
    {
        // Call the ModelViewed event
        ModelViewed::dispatch($manga, $request->ip());

        $manga->load(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
            ->when(auth()->user(), function ($query, $user) use ($manga) {
                $manga->load(['mediaRatings' => function ($query) use ($user) {
                    $query->where([
                        ['user_id', '=', $user->id]
                    ]);
                }, 'library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }])
                    ->loadExists([
                        'favoriters as isFavorited' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }
                    ]);
            });

        $includeArray = [];
        if ($includeInput = $request->input('include')) {
            if (is_string($includeInput)) {
                $includeInput = explode(',', $includeInput);
            }
            $includes = array_unique($includeInput);

            foreach ($includes as $include) {
                switch ($include) {
                    case 'cast':
                        $includeArray['cast'] = function ($query) {
                            $query->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'characters':
                        $includeArray['characters'] = function ($query) {
                            $query->with(['media', 'translation'])
                                ->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-shows':
                        $includeArray['animeRelations'] = function ($query) use ($manga) {
                            $query->with([
                                'related' => function ($query) use ($manga) {
                                    $manga->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation'
                            ])
                                ->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-literatures':
                        $includeArray['mangaRelations'] = function ($query) use ($manga) {
                            $query->with([
                                'related' => function ($query) use ($manga) {
                                    $manga->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation'
                            ])
                                ->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-games':
                        $includeArray['gameRelations'] = function ($query) use ($manga) {
                            $query->with([
                                'related' => function ($query) use ($manga) {
                                    $manga->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation'
                            ])
                                ->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'staff':
                        $includeArray['mediaStaff'] = function ($query) {
                            $query->with(['model', 'staffRole', 'person.media'])
                                ->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'studios':
                        $includeArray['studios'] = function ($query) {
                            $query->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                }
            }
        }
        $manga->loadMissing($includeArray);

        // Show the Manga details response
        return JSONResult::success([
            'data' => LiteratureResource::collection([$manga])
        ]);
    }

    /**
     * Returns detailed information of requested IDs.
     *
     * @param GetIndexRequest $request
     *
     * @return JsonResponse
     */
    public function views(GetIndexRequest $request): JsonResponse
    {
        $data = $request->validated();

        $manga = Manga::whereIn('id', $data['ids'] ?? []);
        $manga->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
            ->when(auth()->user(), function ($query, $user) use ($manga) {
                $manga->with(['mediaRatings' => function ($query) use ($user) {
                    $query->where([
                        ['user_id', '=', $user->id],
                    ]);
                }, 'library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }])
                    ->withExists([
                        'favoriters as isFavorited' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        },
                    ]);
            });

        $includeArray = [];
        if ($includeInput = $request->input('include')) {
            if (is_string($includeInput)) {
                $includeInput = explode(',', $includeInput);
            }
            $includes = array_unique($includeInput);

            foreach ($includes as $include) {
                switch ($include) {
                    case 'cast':
                        $includeArray['cast'] = function ($query) {
                            $query->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'characters':
                        $includeArray['characters'] = function ($query) {
                            $query->with(['media', 'translation'])
                                ->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-shows':
                        $includeArray['animeRelations'] = function ($query) use ($manga) {
                            $query->with([
                                'related' => function ($query) use ($manga) {
                                    $manga->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation'
                            ])
                                ->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-literatures':
                        $includeArray['mangaRelations'] = function ($query) use ($manga) {
                            $query->with([
                                'related' => function ($query) use ($manga) {
                                    $manga->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation'
                            ])
                                ->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'related-games':
                        $includeArray['gameRelations'] = function ($query) use ($manga) {
                            $query->with([
                                'related' => function ($query) use ($manga) {
                                    $manga->viewableViaParent($query)
                                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin']);
                                },
                                'relation'
                            ])
                                ->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'staff':
                        $includeArray['mediaStaff'] = function ($query) {
                            $query->with(['model', 'staffRole', 'person.media'])
                                ->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'studios':
                        $includeArray['studios'] = function ($query) {
                            $query->limit(Manga::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                }
            }
        }
        $manga->with($includeArray);

        // Show the manga details response
        return JSONResult::success([
            'data' => LiteratureResource::collection($manga->get()),
        ]);
    }

    /**
     * Returns manga season.
     *
     * @param GetBrowseSeasonRequest $request
     *
     * @return JsonResponse
     * @throws InvalidEnumKeyException|BindingResolutionException|ConnectionException
     */
    public function browseSeason(GetBrowseSeasonRequest $request)
    {
        // Override parameters
        $request->merge([
            'kind' => BrowseSeasonKind::Manga
        ]);

        // Convert request type
        $app = app();
        $getBrowseSeasonRequest = GetBrowseSeasonRequest::createFrom($request)
            ->setContainer($app) // Necessary or validation fails (validate on null)
            ->setRedirector($app->make(Redirector::class)); // Necessary or validation failure fails (422)
        $getBrowseSeasonRequest->validateResolved(); // Necessary for preparing for validation

        return (new BrowseSeasonController())
            ->view($getBrowseSeasonRequest);
    }

    /**
     * Returns character information of a Manga.
     *
     * @param GetPaginatedRequest $request
     * @param Manga               $manga
     *
     * @return JsonResponse
     */
    public function characters(GetPaginatedRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the characters
        $characters = $manga->characters()
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $characters->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => CharacterResourceIdentity::collection($characters),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the cast information of a Manga.
     *
     * @param GetPaginatedRequest $request
     * @param Manga               $manga
     *
     * @return JsonResponse
     */
    public function cast(GetPaginatedRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the anime cast
        $cast = $manga->cast()
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $cast->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => LiteratureCastResourceIdentity::collection($cast),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-shows information of a Manga.
     *
     * @param GetPaginatedRequest $request
     * @param Manga               $manga
     *
     * @return JsonResponse
     */
    public function relatedShows(GetPaginatedRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the related shows
        $relatedShows = $manga->animeRelations()
            ->with([
                'related' => function ($query) use ($manga) {
                    $manga->viewableViaParent($query)
                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                        ->when(auth()->user(), function ($query, $user) {
                            $query->with(['mediaRatings' => function ($query) use ($user) {
                                $query->where([
                                    ['user_id', '=', $user->id]
                                ]);
                            }, 'library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }])
                                ->withExists([
                                    'favoriters as isFavorited' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    },
                                    'reminderers as isReminded' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    },
                                ]);
                        });
                },
                'relation'
            ])
            ->orderBy(MediaRelation::TABLE_NAME . '.id')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedShows->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => MediaRelatedResource::collection($relatedShows),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-mangas information of a Manga.
     *
     * @param GetPaginatedRequest $request
     * @param Manga               $manga
     *
     * @return JsonResponse
     */
    public function relatedLiteratures(GetPaginatedRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the related mangas
        $relatedLiteratures = $manga->mangaRelations()
            ->with([
                'related' => function ($query) use ($manga) {
                    $manga->viewableViaParent($query)
                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                        ->when(auth()->useR(), function ($query, $user) {
                            $query->with(['mediaRatings' => function ($query) use ($user) {
                                $query->where([
                                    ['user_id', '=', $user->id]
                                ]);
                            }, 'library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }])
                                ->withExists([
                                    'favoriters as isFavorited' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    }
                                ]);
                        });
                },
                'relation'
            ])
            ->orderBy(MediaRelation::TABLE_NAME . '.id')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedLiteratures->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => MediaRelatedResource::collection($relatedLiteratures),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns related-mangas information of a Manga.
     *
     * @param GetPaginatedRequest $request
     * @param Manga               $manga
     *
     * @return JsonResponse
     */
    public function relatedGames(GetPaginatedRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the related mangas
        $relatedGames = $manga->gameRelations()
            ->with([
                'related' => function ($query) use ($manga) {
                    $manga->viewableViaParent($query)
                        ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
                        ->when(auth()->user(), function ($query, $user) {
                            $query->with(['mediaRatings' => function ($query) use ($user) {
                                $query->where([
                                    ['user_id', '=', $user->id]
                                ]);
                            }, 'library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }])
                                ->withExists([
                                    'favoriters as isFavorited' => function ($query) use ($user) {
                                        $query->where('user_id', '=', $user->id);
                                    }
                                ]);
                        });
                },
                'relation'
            ])
            ->orderBy(MediaRelation::TABLE_NAME . '.id')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $relatedGames->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => MediaRelatedResource::collection($relatedGames),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns staff information of a Manga.
     *
     * @param GetPaginatedRequest $request
     * @param Manga               $manga
     *
     * @return JsonResponse
     */
    public function staff(GetPaginatedRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the staff
        $staff = $manga->mediaStaff()
            ->with([
                'person' => function ($query) {
                    $query->with(['media']);
                },
                'staffRole'
            ])
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $staff->nextPageUrl() ?? '');

        // Set model relation
        $staff->each(function ($song) use ($manga) {
            $song->setRelation('model', $manga);
        });

        return JSONResult::success([
            'data' => MediaStaffResource::collection($staff),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the studios information of a Manga.
     *
     * @param GetPaginatedRequest $request
     * @param Manga               $manga
     *
     * @return JsonResponse
     */
    public function studios(GetPaginatedRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        // Get the manga studios
        $mangaStudios = $manga->studios()
            ->with([
                'media',
                'mediaStat',
                'successor',
                'predecessors',
                'tvRating',
            ])
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $mangaStudios->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => StudioResource::collection($mangaStudios),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the more manga made by the same studio.
     *
     * @param GetPaginatedRequest $request
     * @param Manga               $manga
     *
     * @return JsonResponse
     */
    public function moreByStudio(GetPaginatedRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();
        $studioMangas = new CursorPaginator(collect(), $data['limit'] ?? 25);

        // Get the manga studios
        if ($mangaStudio = $manga->studios()->firstWhere('is_studio', '=', true)) {
            $studioMangas = $mangaStudio->manga()
                ->where('model_id', '!=', $manga->id)
                ->cursorPaginate($data['limit'] ?? 25);
        } else if ($mangaStudio = $manga->studios()->first()) {
            $studioMangas = $mangaStudio->manga()
                ->where('model_id', '!=', $manga->id)
                ->cursorPaginate($data['limit'] ?? 25);
        }

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $studioMangas->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => LiteratureResourceIdentity::collection($studioMangas),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds a rating for a Manga item
     *
     * @param RateModelRequest $request
     * @param Manga            $manga
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function rate(RateModelRequest $request, Manga $manga): JsonResponse
    {
        $user = auth()->user();

        // Check if the user is already tracking the manga
        if ($user->hasNotTracked($manga)) {
            throw new AuthorizationException(__('Please add ":x" to your library first.', ['x' => $manga->title]));
        }

        $data = $request->validated();
        $user->rateMediaModel($manga, $data['rating'], $data['description'] ?? null);

        return JSONResult::success();
    }

    /**
     * Delete the user's media rating associated with the given model.
     *
     * @param Manga $manga
     *
     * @return JsonResponse
     */
    public function deleteRating(Manga $manga)
    {
        auth()->user()->mediaRatings()
            ->where([
                ['model_id', '=', $manga->id],
                ['model_type', '=', $manga->getMorphClass()],
            ])
            ->forceDelete();

        return JSONResult::success();
    }

    /**
     * Retrieves upcoming Manga results
     *
     * @param GetPaginatedRequest $request
     *
     * @return JsonResponse
     */
    public function upcoming(GetPaginatedRequest $request): JsonResponse
    {
        $data = $request->validated();

        $manga = Manga::upcoming(-1)
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $manga->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => LiteratureResourceIdentity::collection($manga),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the reviews of a Manga.
     *
     * @param GetPaginatedRequest $request
     * @param Manga               $manga
     *
     * @return JsonResponse
     */
    public function reviews(GetPaginatedRequest $request, Manga $manga): JsonResponse
    {
        $data = $request->validated();

        $reviews = $manga->mediaRatings()
            ->withoutTvRatings()
            ->with([
                'user' => function ($query) {
                    $query->withProfileEagerLoad(auth()->user());
                }
            ])
            ->where('description', '!=', null)
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $reviews->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => MediaRatingResource::collection($reviews),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
