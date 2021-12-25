<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\GetExplorePageRequest;
use App\Http\Resources\ExploreCategoryResource;
use App\Models\ExploreCategory;
use App\Models\Genre;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        // Get explore page for a specific genre
        if ($request->has('genre_id')) {
            $genre = Genre::find($request->input('genre_id'));

            if (empty($genre)) {
                throw new ModelNotFoundException();
            }

            $categories = ExploreCategory::where('is_global', true)
                ->orderBy('position')
                ->get();
        }
        // Get global explore page
        else {
            $categories = ExploreCategory::orderBy('position')->get();
        }

        return JSONResult::success([
            'data' => ExploreCategoryResource::collection(($categories))
        ]);
    }
}
