<?php

namespace App\Http\Controllers\Web;

use App\Actions\Web\Auth\DisableTwoFactorAuthentication;
use App\Actions\Web\Auth\EnableTwoFactorAuthentication;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Enable two-factor authentication for the user.
     *
     * @param Request                       $request
     * @param EnableTwoFactorAuthentication $enable
     *
     * @return JsonResponse|RedirectResponse
     */
    public function store(Request $request, EnableTwoFactorAuthentication $enable): JsonResponse|RedirectResponse
    {
        $enable($request->user(), $request->boolean('force', false));

        return $request->wantsJson()
            ? JSONResult::success()
            : back()->with('status', 'two-factor-authentication-enabled');
    }

    /**
     * Disable two-factor authentication for the user.
     *
     * @param Request                        $request
     * @param DisableTwoFactorAuthentication $disable
     *
     * @return JsonResponse|RedirectResponse
     */
    public function destroy(Request $request, DisableTwoFactorAuthentication $disable): JsonResponse|RedirectResponse
    {
        $disable($request->user());

        return $request->wantsJson()
            ? new JsonResponse('', 200)
            : back()->with('status', 'two-factor-authentication-disabled');
    }
}
