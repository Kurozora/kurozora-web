<?php

namespace App\Http\Controllers;

use App\ExplorePageCategory;
use App\Helpers\JSONResult;
use App\Http\Resources\ExplorePageCategoryResource;
use Illuminate\Http\JsonResponse;

class ExplorePageController extends Controller
{
    // Cache key to remember the explore page response in
    const CACHE_KEY = 'explore-page';

    /**
     * Returns the necessary data for the Anime explore page
     *
     * @return JsonResponse
     */
    function explore() {
        // Retrieve the categories
        $categories = ExplorePageCategory::orderBy('position')->get();

        // Return the response
        return JSONResult::success([
            'categories' => ExplorePageCategoryResource::collection(($categories))
        ]);
    }
}
