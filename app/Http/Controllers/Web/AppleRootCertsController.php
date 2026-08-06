<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AppleRootCertsController extends Controller
{
    /**
     * Handle the refresh operation for the Apple root certificates.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function refresh(Request $request): RedirectResponse
    {
        if (!$request->user() || !$request->user()->can('viewNova')) {
            abort(403);
        }

        Artisan::call('refresh:apple_root_certs');

        return back();
    }
}
