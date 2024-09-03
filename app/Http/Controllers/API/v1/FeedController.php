<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetFeedMessagesExploreRequest;
use App\Http\Requests\GetFeedMessagesHomeRequest;
use App\Http\Requests\PostFeedRequest;
use App\Http\Resources\FeedMessageResource;
use App\Models\FeedMessage;
use App\Models\User;
use App\Notifications\NewFeedMessageReply;
use App\Notifications\NewFeedMessageReShare;
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
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function post(PostFeedRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the auth user
        $user = auth()->user();

        // Check if the message has already been re-shared as user is allowed only one re-share per message
        if ($data['is_reshare'] ?? false) {
            $reShareExists = FeedMessage::where('parent_feed_message_id', '=', $data['parent_id'])
                ->where('user_id', $user->id)
                ->where('is_reshare', true)
                ->exists();

            if ($reShareExists) {
                throw new AuthorizationException(__('You are not allowed to re-share a message more than once.'));
            }
        }

        // Create the feed message
        $feedMessage = $user->feed_messages()->create([
            'parent_feed_message_id'    => $data['parent_id'] ?? null,
            'content'                   => $request->input('content') ?? $request->input('body'),
            'is_reply'                  => $data['is_reply'] ?? false,
            'is_reshare'                => $data['is_reshare'] ?? false,
            'is_nsfw'                   => $data['is_nsfw'] ?? false,
            'is_spoiler'                => $data['is_spoiler'] ?? false,
        ]);

        if ($data['is_reply'] ?? false) {
            // Get parent message
            $parentMessage = FeedMessage::firstWhere('id', '=', $data['parent_id']);

            // Notify user of the reply if the message doesn't belong to the current user
            if ($parentMessage->user->id != $user->id) {
                $parentMessage->user->notify(new NewFeedMessageReply($feedMessage));
            }
        } else if ($data['is_reshare'] ?? false) {
            // Get parent message
            $parentMessage = FeedMessage::firstWhere('id', '=', $data['parent_id']);

            // // Notify user of the re-share if the message doesn't belong to the current user
            if ($parentMessage->user->id != $user->id) {
                $parentMessage->user->notify(new NewFeedMessageReShare($feedMessage));
            }
        }

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
        ]);

        return JSONResult::success([
            'data' => FeedMessageResource::collection([$feedMessage]),
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
     * @param GetFeedMessagesHomeRequest $request
     * @return JsonResponse
     */
    function home(GetFeedMessagesHomeRequest $request): JsonResponse
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
                        ->when(auth()->user(), function ($query, $user) {
                            $query->withExists(['reShares as isReShared' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        });
                }
            ])
            ->withCount(['replies', 'reShares'])
            ->when(auth()->user(), function ($query, $user) {
                $query->withExists(['reShares as isReShared' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->whereIn('user_id', $userIDs)
            ->orderByDesc('created_at')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $feed->nextPageUrl());

        return JSONResult::success([
            'data' => FeedMessageResource::collection($feed),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns the global feed.
     *
     * @param GetFeedMessagesExploreRequest $request
     * @return JsonResponse
     */
    function explore(GetFeedMessagesExploreRequest $request): JsonResponse
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
                        ->when(auth()->user(), function ($query, $user) {
                            $query->withExists(['reShares as isReShared' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        });
                }
            ])
            ->withCount(['replies', 'reShares'])
            ->when(auth()->user(), function ($query, $user) {
                $query->withExists(['reShares as isReShared' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->orderByDesc('created_at')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $feed->nextPageUrl());

        return JSONResult::success([
            'data' => FeedMessageResource::collection($feed),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
