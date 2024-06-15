<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TwoFactorSecretKeyController extends Controller
{
    /**
     * Get the current user's two-factor authentication setup / secret key.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        if (is_null($request->user()->two_factor_secret)) {
            abort(404, __('Two-factor authentication has not been enabled.'));
        }

        return response()->json([
            'secretKey' => decrypt($request->user()->two_factor_secret),
        ]);
    }
}
