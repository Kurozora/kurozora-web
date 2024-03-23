<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Resources\MediaRatingResource;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaRating;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;

class MediaRatingController extends Controller
{
    /**
     * Shows song details.
     *
     * @param MediaRating $mediaRating
     * @return JsonResponse
     */
    public function details(MediaRating $mediaRating): JsonResponse
    {
        // Get the feed messages
        $mediaRating = $mediaRating
            ->load([
                'user'=> function ($query) {
                    $query->with([
                        'badges' => function ($query) {
                            $query->with(['media']);
                        },
                        'media',
                        'tokens' => function ($query) {
                            $query
                                ->orderBy('last_used_at', 'desc')
                                ->limit(1);
                        },
                        'sessions' => function ($query) {
                            $query
                                ->orderBy('last_activity', 'desc')
                                ->limit(1);
                        },
                    ])
                        ->withCount(['followers', 'following', 'mediaRatings']);
                },
                'model' => function (MorphTo $morphTo) {
                    $morphTo->constrain([
                        Anime::class => function (Builder $query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
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
                        Character::class => function (Builder $query) {
                            $query->with(['media', 'mediaStat']);
                        },
                        Episode::class => function (Builder $query) {
                            $query->with([
                                'anime' => function ($query) {
                                    $query->with(['media', 'translations']);
                                },
                                'media',
                                'mediaRatings',
                                'mediaStat',
                                'season' => function ($query) {
                                    $query->with(['media', 'translations']);
                                },
                                'translations',
                                'videos',
                            ]);
                        },
                        Game::class => function (Builder $query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
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
                        Manga::class => function (Builder $query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
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
                        Person::class => function (Builder $query) {
                            $query->with(['media', 'mediaStat']);
                        },
                        Song::class => function (Builder $query) {
                            $query->with(['media', 'mediaStat']);
                        },
                        Studio::class => function (Builder $query) {
                            $query->with(['media', 'mediaStat']);
                        },
                    ]);
                }
            ]);

        return JSONResult::success([
            'data' => MediaRatingResource::collection([$mediaRating])
        ]);
    }
}
