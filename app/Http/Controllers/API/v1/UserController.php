<?php

namespace App\Http\Controllers\API\v1;

use App\Contracts\DeletesUsers;
use App\Events\UserViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\GetFeedMessagesRequest;
use App\Http\Requests\ResetPassword;
use App\Http\Resources\FeedMessageResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceIdentity;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
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
