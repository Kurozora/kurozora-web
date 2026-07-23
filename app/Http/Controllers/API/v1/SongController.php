<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\SearchScope;
use App\Enums\SearchType;
use App\Events\ModelViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRequest;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Requests\RateModelRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\UpdateSongAppleMusicIDRequest;
use App\Http\Resources\AnimeResource;
use App\Http\Resources\GameResource;
use App\Http\Resources\MediaRatingResource;
use App\Http\Resources\SongLyricResource;
use App\Http\Resources\SongResource;
use App\Models\Song;
use App\Traits\Controller\WithCatalogCacheHeaders;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class SongController extends Controller
{
    use WithCatalogCacheHeaders;

    /**
     * Returns the songs index.
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
                    SearchType::Songs
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
     * Shows song details.
     *
     * @param Request $request
     * @param Song    $song
     *
     * @return JsonResponse
     */
    public function view(Request $request, Song $song): JsonResponse
    {
        $includeInput = $request->input('include');
        $includes = is_string($includeInput) ? explode(',', $includeInput) : (is_array($includeInput) ? $includeInput : []);
        sort($includes);

        $fingerprint = [
            'kind' => 'song',
            'publicId' => $song->public_id,
            'updatedAt' => optional($song->updated_at)->toIso8601String(),
            'locale' => app()->getLocale(),
            'tvRating' => (int) $request->attributes->get('tvRating', 4),
            'include' => $includes,
        ];

        $notModified = $this->returnIfNotModifiedCatalog($request, $fingerprint);
        if ($notModified !== null) {
            return $notModified;
        }

        // Call the ModelViewed event
        ModelViewed::dispatch($song, $request->ip());

        $song->load(['media', 'mediaStat', 'translation'])
            ->when(auth()->user(), function ($query, $user) use ($song) {
                $song->load(['mediaRatings' => function ($query) use ($user) {
                    $query->where([
                        ['user_id', '=', $user->id]
                    ]);
                }]);
            });

        return JSONResult::success([
            'data' => SongResource::collection([$song])
        ])->withHeaders($this->catalogCacheHeaders($request, $fingerprint));
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

        $song = Song::whereIn('id', $data['ids'] ?? []);
        $song->with(['media', 'mediaStat', 'translation'])
            ->when(auth()->user(), function ($query, $user) use ($song) {
                $song->with(['mediaRatings' => function ($query) use ($user) {
                    $query->where([
                        ['user_id', '=', $user->id],
                    ]);
                }]);
            });

        // Show the anime details response
        return JSONResult::success([
            'data' => SongResource::collection($song->get()),
        ]);
    }

    /**
     * Returns anime information for a Song
     *
     * @param GetPaginatedRequest $request
     * @param Song                 $song
     *
     * @return JsonResponse
     */
    public function anime(GetPaginatedRequest $request, Song $song): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $animes = $song->anime()
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
            })
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $animes->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => AnimeResource::collection($animes),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns anime information for a Song
     *
     * @param GetPaginatedRequest $request
     * @param Song                $song
     *
     * @return JsonResponse
     */
    public function games(GetPaginatedRequest $request, Song $song): JsonResponse
    {
        $data = $request->validated();

        // Get the games
        $games = $song->games()
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
            })
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $games->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => GameResource::collection($games),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds a rating for a Song item
     *
     * @param RateModelRequest $request
     * @param Song             $song
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function rate(RateModelRequest $request, Song $song): JsonResponse
    {
        $user = auth()->user();

        $data = $request->validated();
        $user->rateMediaModel($song, $data['rating'] ?? 0, $data['description'] ?? null);

        return JSONResult::success();
    }

    /**
     * Delete the user's media rating associated with the given model.
     *
     * @param Song $song
     *
     * @return JsonResponse
     */
    public function deleteRating(Song $song)
    {
        auth()->user()->mediaRatings()
            ->where([
                ['model_id', '=', $song->id],
                ['model_type', '=', $song->getMorphClass()],
            ])
            ->first()?->delete();

        return JSONResult::success();
    }

    /**
     * Updates the Apple Music ID of the given song.
     *
     * @param UpdateSongAppleMusicIDRequest $request
     * @param Song                          $song
     *
     * @return JsonResponse
     */
    public function updateAppleMusicID(UpdateSongAppleMusicIDRequest $request, Song $song): JsonResponse
    {
        $song->am_id = $request->input('am_id');
        $song->save();

        return JSONResult::success();
    }

    /**
     * Returns the synced lyrics of the given song.
     *
     * @param Request $request
     * @param Song    $song
     *
     * @return JsonResponse
     */
    public function lyrics(Request $request, Song $song): JsonResponse
    {
        $lyric = $song->lyrics()
            ->where('status', 'approved')
            ->with(['lines.words'])
            ->orderByDesc('id')
            ->first();

        return JSONResult::success([
            'data' => $lyric === null ? [] : [SongLyricResource::make($lyric)],
        ]);
    }

    /**
     * Returns the reviews of a Song.
     *
     * @param GetPaginatedRequest $request
     * @param Song                  $song
     *
     * @return JsonResponse
     */
    public function reviews(GetPaginatedRequest $request, Song $song): JsonResponse
    {
        $data = $request->validated();

        $reviews = $song->mediaRatings()
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
