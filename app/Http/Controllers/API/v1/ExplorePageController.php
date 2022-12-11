<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
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
    function index(GetExplorePageRequest $request): JsonResponse
    {
        // Get explore categories
        $exploreCategories = ExploreCategory::orderBy('position');

        // Check if categories should be genre or theme specific.
        if ($request->has('genre_id') || $request->has('theme_id')) {
            $exploreCategories->where('is_global', true);
        }

        return JSONResult::success([
            'data' => ExploreCategoryResource::collection($exploreCategories->get())
        ]);
    }

    /**
     * Returns the details of the specified explore category.
     *
     * @param ExploreCategory $exploreCategory
     * @return JsonResponse
     */
    function details(ExploreCategory $exploreCategory): JsonResponse
    {
        return JSONResult::success([
            'data' => ExploreCategoryResource::collection([$exploreCategory])
        ]);
    }
}
