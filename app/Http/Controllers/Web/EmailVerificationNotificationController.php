<?php

namespace App\Http\Controllers\Web;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     *
     * @param Request $request
     *
     * @return JsonResponse|RedirectResponse
     */
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? JSONResult::success()
                : redirect()->intended();
        }

        $request->user()->sendEmailVerificationNotification();

        return $request->wantsJson()
            ? JSONResult::success()
            : back()->with('status', 'verification-link-sent');
    }
}
