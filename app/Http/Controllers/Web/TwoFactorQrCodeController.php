<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorQrCodeController extends Controller
{
    /**
     * Get the SVG element for the user's two-factor authentication QR code.
     *
     * @param Request $request
     * @return array|Response
     */
    public function show(Request $request): array|Response
    {
        if (is_null($request->user()->two_factor_secret)) {
            return [];
        }

        return response()->json([
            'svg' => $request->user()->twoFactorQrCodeSvg(),
            'url' => $request->user()->twoFactorQrCodeUrl(),
        ]);
    }
}
