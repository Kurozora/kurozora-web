<?php

namespace App\Http\Controllers\Web;

use App\Actions\Web\Auth\GenerateNewRecoveryCodes;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RecoveryCodeController extends Controller
{
    /**
     * Get the two-factor authentication recovery codes for authenticated user.
     *
     * @param Request $request
     * @return JsonResponse|array
     */
    public function index(Request $request): JsonResponse|array
    {
        if (!$request->user()->two_factor_secret ||
            !$request->user()->two_factor_recovery_codes) {
            return [];
        }

        return response()->json(json_decode(decrypt(
            $request->user()->two_factor_recovery_codes
        ), true));
    }

    /**
     * Generate a fresh set of two-factor authentication recovery codes.
     *
     * @param Request $request
     * @param GenerateNewRecoveryCodes $generate
     * @return RedirectResponse
     */
    public function store(Request $request, GenerateNewRecoveryCodes $generate): RedirectResponse
    {
        $generate($request->user());

        return back()->with('status', 'recovery-codes-generated');
    }
}
