<?php

namespace App\Http\Controllers\Web;

use App\Events\ModelViewed;
use App\Http\Controllers\Controller;
use App\Models\Platform;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    /**
     * Show a platform's page.
     *
     * @param Request  $request
     * @param Platform $platform
     *
     * @return Application|Factory|View
     */
    public function show(Request $request, Platform $platform): Application|Factory|View
    {
        ModelViewed::dispatch($platform, $request->ip());

        return view('platform.details', [
            'platform' => $platform->load(['media']),
        ]);
    }
}
