<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Show the signed-in user's notifications.
     *
     * @param Request $request
     *
     * @return Application|Factory|View
     */
    public function index(Request $request): Application|Factory|View
    {
        return view('notifications.index', [
            'notifications' => $request->user()
                ->notifications()
                ->with(['notifier'])
                ->latest()
                ->paginate(25)
                ->withQueryString(),
        ]);
    }
}
