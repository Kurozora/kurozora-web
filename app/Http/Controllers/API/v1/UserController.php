<?php

namespace App\Http\Controllers\API\v1;

use App\Contracts\DeletesUsers;
use App\Events\ModelViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Requests\GetUserIndexRequest;
use App\Http\Requests\ResetPassword;
use App\Http\Resources\FeedMessageResource;
use App\Http\Resources\MediaRatingResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceIdentity;
use App\Models\FeedMessage;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    use WithStateVersionETag;

    /**
     * Return the user index.
     *
     * @param GetUserIndexRequest $request
     *
     * @return JsonResponse
     */
    public function index(GetUserIndexRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['ids'])) {
            return $this->views($request);
        }

        $authUser = auth()->user();

        $users = User::visibleTo($authUser)
            ->withProfileEagerLoad($authUser)
            ->sortViaRequest($request)
            ->orderBy('id')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $users->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => UserResource::collection($users),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Returns the profile details for a user
     *
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function profile(Request $request, User $user): JsonResponse
    {
        // Call the ModelViewed event
        ModelViewed::dispatch($user, $request->ip());

        $user->loadProfileEagerLoad(auth()->user());

        // Show profile response
        return JSONResult::success([
            'data' => UserResource::collection([$user])
        ]);
    }

    /**
     * Returns the profile details for a user
     *
     * @param GetUserIndexRequest $request
     *
     * @return JsonResponse
     */
    public function views(GetUserIndexRequest $request): JsonResponse
    {
        $data = $request->validated();
        $authUser = auth()->user();

        $users = User::whereIn('id', $data['ids'] ?? [])
            ->withProfileEagerLoad($authUser);

        // Show the character details response
        return JSONResult::success([
            'data' => UserResource::collection($users->get()),
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
        $authUser = auth()->user();

        if ($authUser !== null && !$authUser->canInteractWith($user)) {
            return JSONResult::success([
                'data' => UserResourceIdentity::collection([])
            ]);
        }

        // Show profile response
        return JSONResult::success([
            'data' => UserResourceIdentity::collection([$user])
        ]);
    }

    /**
     * Returns the feed messages for a user.
     *
     * @param GetPaginatedRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function getFeedMessages(GetPaginatedRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        $authUser = auth()->user();
        $loveReactantLoader = FeedMessage::loveReactantLoader($authUser);

        // Get the feed messages
        $feedMessages = $user->feedMessages()
            ->with([
                'user' => fn($query) => $this->eagerLoadUser($query),
                'loveReactant' => $loveReactantLoader,
                'parentMessage' => function ($query) use ($loveReactantLoader, $authUser) {
                    $query->with([
                        'user' => fn($query) => $this->eagerLoadUser($query),
                        'loveReactant' => $loveReactantLoader,
                    ])
                        ->when($authUser, $this->authReShareState());
                }
            ])
            ->when($authUser, $this->authReShareState())
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $feedMessages->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => FeedMessageResource::collection($feedMessages),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns a list of the user's ratings.
     *
     * @param GetPaginatedRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function getRatings(GetPaginatedRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        // Get the feed messages
        $mediaRatings = $user->mediaRatings()
            ->addEpisodePublicIdSelect()
            ->with([
                'user' => fn($query) => $this->eagerLoadUser($query)
            ])
            ->orderBy('created_at', 'desc')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $mediaRatings->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => MediaRatingResource::collection($mediaRatings),
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
        $belongsTo->withProfileEagerLoad(auth()->user());
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
        $authUser = auth()->user();

        // Validate the password
        if (!Hash::check($data['password'], $authUser->password)) {
            throw new AuthorizationException(__('This password does not match our records.'));
        }

        // Delete the user and any relevant records
        $deleter->delete($authUser->fresh());

        // Logout the user
        auth()->logout();

        return JSONResult::success();
    }
}
