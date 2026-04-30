<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Requests\PostFeedRequest;
use App\Http\Resources\FeedMessageResource;
use App\Models\FeedMessage;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;

class FeedController extends Controller
{
    /**
     * Post a new message to the feed.
     *
     * @param PostFeedRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function post(PostFeedRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = auth()->user();

        $feedMessage = FeedMessage::createFor($user, [
            'parent_id' => $data['parent_id'] ?? null,
            'content' => $request->input('content') ?? $request->input('body'),
            'is_nsfw' => $data['is_nsfw'] ?? false,
            'is_reply' => $data['is_reply'] ?? false,
            'is_reshare' => $data['is_reshare'] ?? false,
            'is_spoiler' => $data['is_spoiler'] ?? false,
        ]);

        $feedMessage->load([
            'user' => fn($query) => $this->eagerLoadUser($query),
            'loveReactant' => function (BelongsTo $query) {
                $query->with([
                    'reactionCounters',
                    'reactions' => function (HasMany $hasMany) {
                        $hasMany->with(['reacter', 'type']);
                    }
                ]);
            },
            'parentMessage' => function ($query) {
                $query->with([
                    'user' => fn($query) => $this->eagerLoadUser($query),
                    'loveReactant' => function (BelongsTo $query) {
                        $query->with([
                            'reactionCounters',
                            'reactions' => function (HasMany $hasMany) {
                                $hasMany->with(['reacter', 'type']);
                            }
                        ]);
                    }
                ])
                    ->withCount(['replies', 'reShares'])
                    ->when(auth()->user(), $this->authReShareState());
            }
        ]);

        return JSONResult::success([
            'data' => FeedMessageResource::collection([$feedMessage]),
        ]);
    }

    /**
     * Returns the closure for eager loading the auth user's re-share state.
     *
     * @return callable
     */
    private function authReShareState(): callable
    {
        return function ($query, $user) {
            $query
                ->withExists(['simpleReShares as isReShared' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }])
                ->withMax(['simpleReShares as my_reshare_id' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }], 'id');
        };
    }

    /**
     * The closure for eager loading user relations on feed messages.
     *
     * @param BelongsTo $belongsTo
     */
    private function eagerLoadUser(BelongsTo $belongsTo)
    {
        $belongsTo->with([
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
            ->withCount(['followers', 'followedModels as following_count', 'mediaRatings'])
            ->when(auth()->check(), function ($query) {
                $query->withExists(['followers as isFollowed' => function ($query) {
                    $query->where('user_id', '=', auth()->user()->id);
                }]);
            });
    }

    /**
     * Returns the user's personal feed.
     *
     * @param GetPaginatedRequest $request
     *
     * @return JsonResponse
     */
    function home(GetPaginatedRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the auth user
        $user = auth()->user();

        // Get the user IDs of all the users that should appear on the user's personal feed.
        $userIDs = $user->followedModels()
            ->pluck(User::TABLE_NAME . '.id')
            ->add($user->id);

        // Get paginated feed messages that are not a reply
        $feed = FeedMessage::noReplies()
            ->with([
                'user' => fn($query) => $this->eagerLoadUser($query),
                'loveReactant' => function (BelongsTo $query) {
                    $query->with([
                        'reactionCounters',
                        'reactions' => function (HasMany $hasMany) {
                            $hasMany->with(['reacter', 'type']);
                        }
                    ]);
                },
                'parentMessage' => function ($query) {
                    $query->with([
                        'user' => fn($query) => $this->eagerLoadUser($query),
                        'loveReactant' => function (BelongsTo $query) {
                            $query->with([
                                'reactionCounters',
                                'reactions' => function (HasMany $hasMany) {
                                    $hasMany->with(['reacter', 'type']);
                                }
                            ]);
                        }
                    ])
                        ->withCount(['replies', 'reShares'])
                        ->when(auth()->user(), $this->authReShareState());
                }
            ])
            ->withCount(['replies', 'reShares'])
            ->when(auth()->user(), $this->authReShareState())
            ->whereIn('user_id', $userIDs)
            ->orderByDesc('created_at')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $feed->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => FeedMessageResource::collection($feed),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the global feed.
     *
     * @param GetPaginatedRequest $request
     *
     * @return JsonResponse
     */
    function explore(GetPaginatedRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get paginated global feed messages that are not a reply
        $feed = FeedMessage::noReplies()
            ->with([
                'user' => fn($query) => $this->eagerLoadUser($query),
                'loveReactant' => function (BelongsTo $query) {
                    $query->with([
                        'reactionCounters',
                        'reactions' => function (HasMany $hasMany) {
                            $hasMany->with(['reacter', 'type']);
                        }
                    ]);
                },
                'parentMessage' => function ($query) {
                    $query->with([
                        'user' => fn($query) => $this->eagerLoadUser($query),
                        'loveReactant' => function (BelongsTo $query) {
                            $query->with([
                                'reactionCounters',
                                'reactions' => function (HasMany $hasMany) {
                                    $hasMany->with(['reacter', 'type']);
                                }
                            ]);
                        }
                    ])
                        ->withCount(['replies', 'reShares'])
                        ->when(auth()->user(), $this->authReShareState());
                }
            ])
            ->withCount(['replies', 'reShares'])
            ->when(auth()->user(), $this->authReShareState())
            ->orderByDesc('created_at')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $feed->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => FeedMessageResource::collection($feed),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
