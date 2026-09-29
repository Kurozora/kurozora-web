<?php

namespace App\Http\Controllers\Web\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FollowingController extends Controller
{
    /**
     * Show the users a user follows.
     *
     * @param Request $request
     * @param User    $user
     *
     * @return Application|Factory|View
     */
    public function index(Request $request, User $user): Application|Factory|View
    {
        $authUser = $request->user();

        return view('profile.following', [
            'user' => $user,
            'isFollowed' => $authUser !== null && $user->isFollowedBy($authUser),
            'following' => $user->following()
                ->with(['media'])
                ->withCount(['followers'])
                ->when($authUser, function ($query, $authUser) {
                    $query->withExists(['followers as isFollowed' => function ($query) use ($authUser) {
                        $query->where('user_id', '=', $authUser->id);
                    }]);
                })
                ->orderBy(UserFollow::TABLE_NAME . '.created_at', 'desc')
                ->paginate(25),
        ]);
    }
}
