<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\GetExplorePageRequest;
use App\Http\Resources\ExploreCategoryResource;
use App\Models\ExploreCategory;
use Illuminate\Http\JsonResponse;

class ExplorePageController extends Controller
{
    /**
     * Returns the necessary data for the Anime explore page.
     *
     * @param GetExplorePageRequest $request
     * @return JsonResponse
     */
    function explore(GetExplorePageRequest $request): JsonResponse
    {
        // Get explore categories
        $exploreCategories = ExploreCategory::orderBy('position');

        // Check if categories should be genre or theme specific.
        if ($request->has('genre_id') || $request->has('theme_id')) {
            $exploreCategories->where('is_global', true);
        }

        return JSONResult::success([
            'data' => ExploreCategoryResource::collection(($exploreCategories->get()))
        ]);
    }
}
