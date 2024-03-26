<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\FeedVoteType;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\FeedMessageRepliesRequest;
use App\Http\Requests\FeedMessageUpdateRequest;
use App\Http\Resources\FeedMessageResource;
use App\Models\FeedMessage;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;

class FeedMessageController extends Controller
{
    /**
     * Get the feed message details.
     *
     * @param FeedMessage $feedMessage
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
                    ->when(auth()->user(), function ($query, $user) {
                        $query->withExists(['reShares as isReShared' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        }]);
                    });
            }
        ])
            ->loadCount(['replies', 'reShares'])
            ->when(auth()->user(), function ($query, $user) use ($feedMessage) {
                $feedMessage->loadExists(['reShares as isReShared' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            });

        return JSONResult::success([
            'data' => FeedMessageResource::collection([$feedMessage])
        ]);
    }

    /**
     * Update a feed message's details.
     *
     * @param FeedMessageUpdateRequest $request
     * @param FeedMessage $feedMessage
     * @return JsonResponse
     * @throws AuthorizationException
     */
    function update(FeedMessageUpdateRequest $request, FeedMessage $feedMessage): JsonResponse
    {
        $data = $request->validated();

        // Update feed message
        $feedMessage->update([
            'content'       => $request->input('content') ?? $request->input('body'),
            'is_nsfw'       => $data['is_nsfw'],
            'is_spoiler'    => $data['is_spoiler']
        ]);

        // Show successful response
        return JSONResult::success([
            'data' => [
                'content'           => $feedMessage->content,
                'contentHTML'       => $feedMessage->content_html,
                'contentMarkdown'   => $feedMessage->content_markdown,
                'isNSFW'            => (bool) $data['is_nsfw'],
                'isSpoiler'         => (bool) $data['is_spoiler']
            ]
        ]);
    }

    /**
     * Get the replies of the feed message.
     *
     * @param FeedMessageRepliesRequest $request
     * @param FeedMessage $feedMessage
     * @return JsonResponse
     */
    function replies(FeedMessageRepliesRequest $request, FeedMessage $feedMessage): JsonResponse
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
                        ->when(auth()->user(), function ($query, $user) {
                            $query->withExists(['reShares as isReShared' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        });
                }
            ])
            ->withCount(['replies', 'reShares'])
            ->when(auth()->user(), function ($query, $user) use ($feedMessage) {
                $feedMessage->withExists(['reShares as isReShared' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->orderByDesc('created_at')
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $feedMessageReplies->nextPageUrl());

        return JSONResult::success([
            'data' => FeedMessageResource::collection($feedMessageReplies),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
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
            ->withCount(['followers', 'following', 'mediaRatings'])
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
     * @return JsonResponse
     */
    function heart(FeedMessage $feedMessage): JsonResponse
    {
        // Get the authenticated user
        $user = auth()->user();

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
     * Deletes the authenticated user's feed message.
     *
     * @param FeedMessage $feedMessage
     * @return JsonResponse
     * @throws Exception
     */
    public function delete(FeedMessage $feedMessage): JsonResponse
    {
        // Delete the feed message
        $feedMessage->delete();

        return JSONResult::success();
    }
}
