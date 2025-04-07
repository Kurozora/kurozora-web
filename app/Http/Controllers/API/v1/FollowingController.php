<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetPaginatedRequest;
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

        $isFollowed = !is_bool($authUser->toggleFollow($user));

        if ($isFollowed) {
            // Send notification
            $user->notify(new NewFollower($authUser));
        }

        // Successful response
        return JSONResult::success([
            'data' => [
                'isFollowed' => $isFollowed
            ]
        ]);
    }

    /**
     * Returns a list of the user's followers.
     *
     * @param GetPaginatedRequest $request
     * @param User $user
     * @return JsonResponse
     */
    function getFollowers(GetPaginatedRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        // Get the followers
        $followers = $user->followers()
            ->orderBy(UserFollow::TABLE_NAME . '.created_at', 'desc')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $followers->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => UserResourceIdentity::collection($followers),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns a list of the user's following.
     *
     * @param GetPaginatedRequest $request
     * @param User $user
     * @return JsonResponse
     */
    function getFollowing(GetPaginatedRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        // Get the following
        $following = $user->followedModels()
            ->orderBy(UserFollow::TABLE_NAME . '.created_at', 'desc')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $following->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => UserResourceIdentity::collection($following),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
