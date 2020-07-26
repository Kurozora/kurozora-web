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
use Illuminate\Support\Facades\Validator;

class FollowingController extends Controller
{
    /**
     * Follows a user.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    function followUser(Request $request, User $user) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'follow' => 'bail|required|integer|min:0|max:1'
        ]);

        // Check validator
        if($validator->fails())
            return JSONResult::error($validator->errors()->first());

        /** @var User $authUser */
        $authUser = Auth::user();

        $follow = (bool) $request->input('follow');

        $isAlreadyFollowing = $user->followers()->where('user_id', $authUser->id)->exists();

        // Follow the user
        if($follow) {
            // Already following this user
            if($isAlreadyFollowing)
                return JSONResult::error('You are already following this user.');

            // Follow the user
            $user->followers()->attach($authUser);

            $user->notify(new NewFollower($authUser));
        }
        // Unfollow the user
        else {
            // Not following this user
            if(!$isAlreadyFollowing)
                return JSONResult::error('You are not following this user.');

            // Delete follow
            $user->followers()->detach($authUser);
        }

        // Successful response
        return JSONResult::success();
    }

    /**
     * Returns a list of the user's followers.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    function getFollowers(Request $request, User $user) {
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
    function getFollowing(Request $request, User $user) {
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
