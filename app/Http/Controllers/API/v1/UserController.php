<?php

namespace App\Http\Controllers\API\v1;

use App\Contracts\DeletesUsers;
use App\Events\UserViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\GetFeedMessagesRequest;
use App\Http\Requests\GetRatingsRequest;
use App\Http\Requests\ResetPassword;
use App\Http\Resources\FeedMessageResource;
use App\Http\Resources\MediaRatingResource;
use App\Http\Resources\UserResource;
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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    /**
     * Returns the profile details for a user
     *
     * @param User $user
     * @return JsonResponse
     */
    public function profile(User $user): JsonResponse
    {
        // Call the UserViewed event
        UserViewed::dispatch($user);

        $user->load([
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
            ->loadCount(['followers', 'following', 'mediaRatings']);

        // Show profile response
        return JSONResult::success([
            'data' => UserResource::collection([$user])
        ]);
    }

    /**
     * Returns the profile details for a user
     *
     * @param User $user
     * @return JsonResponse
     */
    public function search(User $user): JsonResponse
    {
        // Show profile response
        return JSONResult::success([
            'data' => UserResourceIdentity::collection([$user])
        ]);
    }

    /**
     * Returns the feed messages for a user.
     *
     * @param GetFeedMessagesRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function getFeedMessages(GetFeedMessagesRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        // Get the feed messages
        $feedMessages = $user->feed_messages()
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
            ->orderBy('created_at', 'desc')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $feedMessages->nextPageUrl());

        return JSONResult::success([
            'data' => FeedMessageResource::collection($feedMessages),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns a list of the user's ratings.
     *
     * @param GetRatingsRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function getRatings(GetRatingsRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        // Get the feed messages
        $mediaRatings = $user->mediaRatings()
            ->with([
                'user',
                'model' => function (MorphTo $morphTo) {
                    $morphTo->constrain([
                        Anime::class => function (Builder $query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating']);
                        },
                        Character::class => function (Builder $query) {
                            $query->with(['media', 'mediaStat']);
                        },
                        Episode::class => function (Builder $query) {
                            $query->with(['media', 'mediaStat']);
                        },
                        Game::class => function (Builder $query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating']);
                        },
                        Manga::class => function (Builder $query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating']);
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
            ])
            ->orderBy('created_at', 'desc')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $mediaRatings->nextPageUrl());

        return JSONResult::success([
            'data' => MediaRatingResource::collection($mediaRatings),
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
     * Requests a password reset link to be sent to the email address.
     *
     * @param ResetPassword $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPassword $request): JsonResponse
    {
        $data = $request->validated();

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        Password::sendResetLink(['email' => $data['email']]);

        // Show successful response
        return JSONResult::success();
    }

    /**
     * Deletes the user's account permanently.
     *
     * @param DeleteUserRequest $request
     * @param DeletesUsers $deleter
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function delete(DeleteUserRequest $request, DeletesUsers $deleter): JsonResponse
    {
        $data = $request->validated();

        // Validate the password
        if (!Hash::check($data['password'], auth()->user()->password)) {
            throw new AuthorizationException(__('This password does not match our records.'));
        }

        // Delete the user and any relevant records
        $deleter->delete(auth()->user()->fresh());

        // Logout the user
        auth()->logout();

        return JSONResult::success();
    }
}
