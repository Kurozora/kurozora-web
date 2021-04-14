<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\GetFeedMessagesRequest;
use App\Http\Requests\ResetPassword;
use App\Http\Requests\SearchUserRequest;
use App\Http\Resources\FeedMessageResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceBasic;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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
        // Show profile response
        return JSONResult::success([
            'data' => UserResource::collection([$user])
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
        $feedMessages = $user->feedMessages()
            ->orderByDesc('created_at')
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $feedMessages->nextPageUrl());

        return JSONResult::success([
            'data' => FeedMessageResource::collection($feedMessages),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Requests a password reset link to be sent to the email address
     *
     * @param ResetPassword $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPassword $request): JsonResponse
    {
        $data = $request->validated();

        // Try to find the user with this email
        /** @var User $user */
        $user = User::where('email', $data['email'])->first();

        // There is a user with this email the try to send a reset link.
        // Request may be throttled if requested a lot.
        if ($user) {
            Password::sendResetLink(['email' => $data['email']]);
        }

        // Show successful response
        return JSONResult::success();
    }

    /**
     * Retrieves User search results
     *
     * @param SearchUserRequest $request
     * @return JsonResponse
     */
    public function search(SearchUserRequest $request): JsonResponse
    {
        $searchQuery = $request->input('query');

        // Search for the users
        $users = User::kuroSearch($searchQuery, [
            'limit' => User::MAX_SEARCH_RESULTS
        ]);

        // Show response
        return JSONResult::success([
            'data' => UserResourceBasic::collection($users)
        ]);
    }
}
