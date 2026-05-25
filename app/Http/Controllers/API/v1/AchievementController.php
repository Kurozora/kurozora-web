<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Resources\AchievementResource;
use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Http\JsonResponse;

class AchievementController extends Controller
{
    /**
     * Returns a list of the user's achievements.
     *
     * @param GetPaginatedRequest $request
     * @param User                       $user
     *
     * @return JsonResponse
     */
    public function index(GetPaginatedRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        $achievements = Achievement::achievedByUser($user)
            ->with('media')
            ->orderBy('is_achieved', 'desc')
            ->orderBy(UserAchievement::TABLE_NAME . '.created_at')
            ->orderBy(Achievement::TABLE_NAME . '.name')
            ->orderBy(Achievement::TABLE_NAME . '.id')
            ->cursorPaginate($data['limit'] ?? 25);

        $nextPageURL = str_replace($request->root(), '', $achievements->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => AchievementResource::collection($achievements),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
