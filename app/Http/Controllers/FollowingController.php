<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Resources\UserResourceSmall;
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

        $authUser = Auth::user();

        $follow = (bool) $request->input('follow');

        // Follow the user
        if($follow) {
            // Already following this user
            if($authUser->isFollowing($user))
                return JSONResult::error('You are already following this user.');

            // Follow the user
            $user->followers()->attach($authUser);
        }
        // Unfollow the user
        else {
            // Not following this user
            if(!$authUser->isFollowing($user))
                return JSONResult::error('You are not following this user.');

            // Delete follow
            $user->followers()->detach($authUser);
        }

        // Successful response
        return JSONResult::success();
    }

    /**
     * Returns a list of a user's followers.
     *
     * @param User $user
     * @return JsonResponse
     */
    function getFollowers(User $user) {
        return JSONResult::success([
            'followers' => UserResourceSmall::collection($user->followers)
        ]);
    }
}
