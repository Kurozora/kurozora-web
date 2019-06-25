<?php

namespace App\Http\Controllers;

use App\ExplorePageCategory;
use App\Helpers\JSONResult;
use App\Http\Resources\ExplorePageCategoryResource;

class ExplorePageController extends Controller
{
    // Cache key to remember the explore page response in
    const CACHE_KEY = 'explore-page';

    /**
     * Returns the necessary data for the Anime explore page
     */
    function explore() {
        // Retrieve the categories
        $categories = ExplorePageCategory::all();

        // Return the response
        (new JSONResult())->setData([
            'categories' => ExplorePageCategoryResource::collection(($categories))
        ])->show();
    }
}
