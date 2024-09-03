<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetFollowersRequest;
use App\Http\Requests\GetFollowingRequest;
use App\Http\Resources\UserResourceIdentity;
use App\Models\User;
use App\Models\UserFollow;
use App\Notifications\NewFollower;
use Illuminate\Http\JsonResponse;

class FollowingController extends Controller
{
    /**
     * Follows a user.
     *
     * @param User $user
     * @return JsonResponse
     */
    function followUser(User $user): JsonResponse
    {
        $authUser = auth()->user();

        $isAlreadyFollowing = $authUser->hasFollowed($user);

        if ($isAlreadyFollowing) {
            // Delete follow
            $authUser->follow($user);
        } else {
            // Follow the user
            $authUser->unfollow($user);

            // Send notification
            $user->notify(new NewFollower($authUser));
        }

        // Successful response
        return JSONResult::success([
            'data' => [
                'isFollowed' => !$isAlreadyFollowing
            ]
        ]);
    }

    /**
     * Returns a list of the user's followers.
     *
     * @param GetFollowersRequest $request
     * @param User $user
     * @return JsonResponse
     */
    function getFollowers(GetFollowersRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        // Get the followers
        $followers = $user->followers()
            ->orderBy(UserFollow::TABLE_NAME . '.created_at', 'desc')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $followers->nextPageUrl());

        return JSONResult::success([
            'data' => UserResourceIdentity::collection($followers),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns a list of the user's following.
     *
     * @param GetFollowingRequest $request
     * @param User $user
     * @return JsonResponse
     */
    function getFollowing(GetFollowingRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        // Get the following
        $following = $user->followedModels()
            ->orderBy(UserFollow::TABLE_NAME . '.created_at', 'desc')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $following->nextPageUrl());

        return JSONResult::success([
            'data' => UserResourceIdentity::collection($following),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
