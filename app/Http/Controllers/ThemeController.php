<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Resources\ThemeResource;
use App\Models\Theme;
use Illuminate\Http\JsonResponse;

class ThemeController extends Controller
{
    /**
     * Generate an overview of themes.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Get all themes and format them
        $themes = Theme::orderBy('name')
            ->get();

        // Show themes in response
        return JSONResult::success([
            'data' => ThemeResource::collection($themes)
        ]);
    }

    /**
     * Shows theme details
     *
     * @param Theme $theme
     * @return JsonResponse
     */
    public function details(Theme $theme): JsonResponse
    {
        // Show theme details
        return JSONResult::success([
            'data' => ThemeResource::collection([$theme])
        ]);
    }
}
