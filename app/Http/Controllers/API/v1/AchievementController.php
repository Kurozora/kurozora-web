<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetUserAchievementsRequest;
use App\Http\Resources\AchievementResource;
use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Http\JsonResponse;

class AchievementController extends Controller
{
    /**
     * Returns a list of the user's achievements.
     *
     * @param GetUserAchievementsRequest $request
     * @param User                       $user
     *
     * @return JsonResponse
     */
    public function index(GetUserAchievementsRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        // Get the badges
        $badges = Badge::achievedUserBadges($user)
            ->with('media')
            ->orderBy('is_achieved', 'desc')
            ->orderBy(UserBadge::TABLE_NAME . '.created_at')
            ->orderBy(Badge::TABLE_NAME . '.name')
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $badges->nextPageUrl());

        // Show profile response
        return JSONResult::success([
            'data' => AchievementResource::collection($badges),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
