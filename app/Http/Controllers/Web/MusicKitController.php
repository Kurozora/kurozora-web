<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AppleMusicService;
use Illuminate\Http\JsonResponse;

class MusicKitController extends Controller
{
    /**
     * Returns the MusicKit developer token and app configuration.
     *
     * @param AppleMusicService $appleMusic
     *
     * @return JsonResponse
     */
    public function token(AppleMusicService $appleMusic): JsonResponse
    {
        return response()->json([
            ...$appleMusic->developerToken(),
            'app' => [
                'build' => config('app.version'),
                'icon' => asset('images/static/icon/app_icon.webp'),
                'name' => config('app.name'),
                'version' => config('app.version'),
            ],
        ]);
    }
}
