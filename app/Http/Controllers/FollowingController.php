<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\GetFollowersRequest;
use App\Http\Requests\GetFollowingRequest;
use App\Http\Resources\UserResourceIdentity;
use App\Models\User;
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

        $isAlreadyFollowing = $user->followers()->where('user_id', $authUser->id)->exists();

        if ($isAlreadyFollowing) {
            // Delete follow
            $user->followers()->detach($authUser);
        } else {
            // Follow the user
            $user->followers()->attach($authUser);

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
        $followers = $user->followers()->paginate($data['limit'] ?? 25);

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
        $following = $user->following()->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $following->nextPageUrl());

        return JSONResult::success([
            'data' => UserResourceIdentity::collection($following),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
