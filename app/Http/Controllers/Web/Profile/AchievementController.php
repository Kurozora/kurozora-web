<?php

namespace App\Http\Controllers\Web\Profile;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class AchievementController extends Controller
{
    /**
     * Show a user's achievements.
     *
     * @param User $user
     *
     * @return Application|Factory|View
     */
    public function index(User $user): Application|Factory|View
    {
        return view('profile.achievements', [
            'user' => $user,
            'achievements' => Achievement::achievedByUser($user)
                ->with('media')
                ->orderBy('is_achieved', 'desc')
                ->orderBy(UserAchievement::TABLE_NAME . '.created_at')
                ->orderBy(Achievement::TABLE_NAME . '.name')
                ->paginate(25),
        ]);
    }
}
