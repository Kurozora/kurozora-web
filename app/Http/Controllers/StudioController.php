<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetStudio;
use App\Studio;
use App\Helpers\JSONResult;
use App\Http\Resources\StudioResource;
use Illuminate\Http\JsonResponse;

class StudioController extends Controller
{
    /**
     * Generate an overview of studios.
     *
     * @param \App\Http\Requests\GetStudio $request
     *
     * @return JsonResponse
     */
    public function overview(GetStudio $request) {
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
     * @param \App\Http\Requests\GetStudio $request
     * @param Studio                       $studio
     *
     * @return JsonResponse
     */
    public function details(GetStudio $request, Studio $studio) {
        // Show studio details
        return JSONResult::success([
            'studio' => StudioResource::make($studio)
        ]);
    }
}
