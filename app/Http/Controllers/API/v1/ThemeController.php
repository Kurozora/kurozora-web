<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRequest;
use App\Http\Resources\ThemeResource;
use App\Models\Theme;
use Illuminate\Http\JsonResponse;

class ThemeController extends Controller
{
    /**
     * Generate an overview of themes.
     *
     * @param GetIndexRequest $request
     *
     * @return JsonResponse
     */
    public function index(GetIndexRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['ids'])) {
            return $this->views($request);
        } else {
            // Get all themes
            $themes = Theme::orderBy('name')
                ->with(['media'])
                ->get();

            // Show themes in response
            return JSONResult::success([
                'data' => ThemeResource::collection($themes)
            ]);
        }
    }

    /**
     * Returns detailed information of requested IDs.
     *
     * @param GetIndexRequest $request
     *
     * @return JsonResponse
     */
    public function views(GetIndexRequest $request): JsonResponse
    {
        $data = $request->validated();

        $theme = Theme::whereIn('id', $data['ids'] ?? []);
        $theme->with(['media']);

        // Show the theme details response
        return JSONResult::success([
            'data' => ThemeResource::collection($theme->get()),
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
        $theme->load(['media']);

        // Show theme details
        return JSONResult::success([
            'data' => ThemeResource::collection([$theme])
        ]);
    }
}
