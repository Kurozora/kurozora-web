<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Resources\UserResourceBasic;
use App\Notifications\NewFollower;
use App\User;
use App\UserFollow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        /** @var User $authUser */
        $authUser = Auth::user();

        $isAlreadyFollowing = $user->followers()->where('user_id', $authUser->id)->exists();

        if($isAlreadyFollowing) {
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
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    function getFollowers(Request $request, User $user): JsonResponse
    {
        // Get the followers
        $followers = $user->followers()->paginate(UserFollow::AMOUNT_OF_FOLLOWERS_PER_PAGE);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $followers->nextPageUrl());

        return JSONResult::success([
            'data' => UserResourceBasic::collection($followers),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns a list of the user's following.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    function getFollowing(Request $request, User $user): JsonResponse
    {
        // Get the following
        $following = $user->following()->paginate(UserFollow::AMOUNT_OF_FOLLOWERS_PER_PAGE);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $following->nextPageUrl());

        return JSONResult::success([
            'data' => UserResourceBasic::collection($following),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
