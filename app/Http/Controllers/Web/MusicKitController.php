<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AppleMusicService;
use Illuminate\Http\JsonResponse;

class MusicKitController extends Controller
{
    public function token(AppleMusicService $appleMusic): JsonResponse
    {
        return response()->json($appleMusic->developerToken());
    }
}
