<?php

namespace App\Http\Controllers;

use App\Studio;
use App\Helpers\JSONResult;
use App\Http\Resources\StudioResource;
use Illuminate\Http\JsonResponse;

class StudioController extends Controller
{
    /**
     * Generate an overview of studios.
     *
     * @return JsonResponse
     */
    public function overview() {
        // Get all studios and format them
        $allStudios = Studio::get()->map(function($studio) {
            return StudioResource::make($studio);
        });

        // Show studios in response
        return JSONResult::success(['studios' => $allStudios]);
    }

    /**
     * Shows studio details
     *
     * @param Studio $studio
     *
     * @return JsonResponse
     */
    public function details(Studio $studio) {
        // Show studio details
        return JSONResult::success([
            'studio' => StudioResource::make($studio)
        ]);
    }
}
