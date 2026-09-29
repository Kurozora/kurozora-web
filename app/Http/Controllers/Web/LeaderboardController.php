<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class LeaderboardController extends Controller
{
    /**
     * Show the users ranked by reputation.
     *
     * @return Application|Factory|View
     */
    public function reputation(): Application|Factory|View
    {
        return view('leaderboards.reputation', [
            'users' => User::with(['media'])
                ->withCount(['followers'])
                ->when(auth()->user(), function ($query, $user) {
                    $query->withExists(['followers as isFollowed' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    }]);
                })
                ->orderByDesc('reputation_count')
                ->orderBy('id')
                ->paginate(100),
        ]);
    }
}
