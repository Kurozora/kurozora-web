<?php

namespace App\Http\Controllers\Web\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBlock;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BlockedController extends Controller
{
    /**
     * Show the accounts a user has blocked.
     *
     * @param Request $request
     * @param User    $user
     *
     * @return Application|Factory|View
     */
    public function index(Request $request, User $user): Application|Factory|View
    {
        abort_if($user->id !== $request->user()?->id, 403);

        return view('profile.blocked', [
            'user' => $user,
            'blockedUsers' => $user->blockedModels()
                ->with(['media'])
                ->withCount(['followers'])
                ->orderBy(UserBlock::TABLE_NAME . '.created_at', 'desc')
                ->paginate(25),
        ]);
    }
}
