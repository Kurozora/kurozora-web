<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\FeedVoteType;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\FeedMessageUpdateRequest;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Requests\GetSortedPaginatedRequest;
use App\Http\Resources\FeedMessageResource;
use App\Models\FeedMessage;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Throwable;

class FeedMessageController extends Controller
{
    /**
     * Get the feed message details.
     *
     * @param FeedMessage $feedMessage
     *
     * @return JsonResponse
     */
    function details(FeedMessage $feedMessage): JsonResponse
    {
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
        ])
            ->loadCount(['replies', 'reShares']);

        if ($user = auth()->user()) {
            $feedMessage->loadExists(['simpleReShares as isReShared' => function ($query) use ($user) {
                $query->where('user_id', '=', $user->id);
            }])
                ->loadMax(['simpleReShares as my_reshare_id' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }], 'id');
        }

        return JSONResult::success([
            'data' => FeedMessageResource::collection([$feedMessage])
        ]);
    }

    /**
     * Update a feed message's details.
     *
     * @param FeedMessageUpdateRequest $request
     * @param FeedMessage              $feedMessage
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    function update(FeedMessageUpdateRequest $request, FeedMessage $feedMessage): JsonResponse
    {
        $data = $request->validated();

        // Update feed message
        $feedMessage->update([
            'content' => $request->input('content') ?? $request->input('body'),
            'is_nsfw' => $data['is_nsfw'],
            'is_spoiler' => $data['is_spoiler']
        ]);

        // Show successful response
        return JSONResult::success([
            'data' => [
                'content' => $feedMessage->content,
                'contentHTML' => $feedMessage->content_html,
                'contentMarkdown' => $feedMessage->content_markdown,
                'isNSFW' => (bool) $data['is_nsfw'],
                'isSpoiler' => (bool) $data['is_spoiler']
            ]
        ]);
    }

    /**
     * Get the replies of the feed message.
     *
     * @param GetPaginatedRequest $request
     * @param FeedMessage         $feedMessage
     *
     * @return JsonResponse
     */
    function replies(GetPaginatedRequest $request, FeedMessage $feedMessage): JsonResponse
    {
        $data = $request->validated();

        // Get the feed message replies
        $feedMessageReplies = $feedMessage->replies()
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
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $feedMessageReplies->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => FeedMessageResource::collection($feedMessageReplies),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Get the quote re-shares of the feed message.
     *
     * @param GetSortedPaginatedRequest $request
     * @param FeedMessage               $feedMessage
     *
     * @return JsonResponse
     */
    function quotes(GetSortedPaginatedRequest $request, FeedMessage $feedMessage): JsonResponse
    {
        return $this->paginatedReShares($request, $feedMessage, simple: false);
    }

    /**
     * Get the simple re-shares of the feed message.
     *
     * @param GetSortedPaginatedRequest $request
     * @param FeedMessage               $feedMessage
     *
     * @return JsonResponse
     */
    function reShares(GetSortedPaginatedRequest $request, FeedMessage $feedMessage): JsonResponse
    {
        return $this->paginatedReShares($request, $feedMessage, simple: true);
    }

    /**
     * Get the paginated re-shares of the feed message.
     *
     * @param GetSortedPaginatedRequest $request
     * @param FeedMessage               $feedMessage
     * @param bool                      $simple
     *
     * @return JsonResponse
     */
    private function paginatedReShares(GetSortedPaginatedRequest $request, FeedMessage $feedMessage, bool $simple): JsonResponse
    {
        $data = $request->validated();
        $sort = $data['sort'] ?? 'recent';

        $query = ($simple ? $feedMessage->simpleReShares() : $feedMessage->quoteReShares())
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
            ->when(auth()->user(), $this->authReShareState());

        match ($sort) {
            'top' => $query->orderByDesc('ranking_score')->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at'),
        };

        $paginator = $query->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        $nextPageURL = str_replace($request->root(), '', $paginator->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => FeedMessageResource::collection($paginator),
            'next' => empty($nextPageURL) ? null : $nextPageURL
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
     * Heart a feed message.
     *
     * @param FeedMessage $feedMessage
     *
     * @return JsonResponse
     */
    function heart(FeedMessage $feedMessage): JsonResponse
    {
        // Get the authenticated user
        $user = auth()->user();

        // Check if engagement must be blocked
        $feedMessage->loadMissing('user');

        if (!$user->canInteractWith($feedMessage->user)) {
            throw new AuthorizationException(__('You are not allowed to engage with this user.'));
        }

        // Get the vote
        $voteAction = $user->toggleHeart($feedMessage);

        // Show successful response
        return JSONResult::success([
            'data' => [
                'isHearted' => $voteAction == FeedVoteType::Heart
            ]
        ]);
    }

    /**
     * Pin a feed message to profile.
     *
     * @param FeedMessage $feedMessage
     *
     * @return JsonResponse
     *
     * @throws Throwable
     */
    function pin(FeedMessage $feedMessage): JsonResponse
    {
        // Get the authenticated user
        $user = auth()->user();

        // Get the vote
        $pinAction = $user->togglePin($feedMessage);

        // Show successful response
        return JSONResult::success([
            'data' => [
                'isPinned' => $pinAction
            ]
        ]);
    }

    /**
     * Deletes the authenticated user's feed message.
     *
     * @param FeedMessage $feedMessage
     *
     * @return JsonResponse
     */
    public function delete(FeedMessage $feedMessage): JsonResponse
    {
        // Delete the feed message
        $feedMessage->delete();

        return JSONResult::success();
    }
}
