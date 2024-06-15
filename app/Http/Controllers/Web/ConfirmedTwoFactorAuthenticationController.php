<?php

namespace App\Http\Controllers\Web;

use App\Actions\Web\Auth\ConfirmTwoFactorAuthentication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class ConfirmedTwoFactorAuthenticationController extends Controller
{
    /**
     * Enable two-factor authentication for the user.
     *
     * @param Request $request
     * @param ConfirmTwoFactorAuthentication $confirm
     * @return Response
     */
    public function store(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $confirm($request->user(), $request->input('code'));

        return $request->wantsJson()
            ? new JsonResponse('', 200)
            : back()->with('status', 'two-factor-authentication-confirmed');
    }
}
