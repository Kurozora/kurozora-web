<?php

namespace App\Http\Controllers;

use App\Enums\ExploreCategoryTypes;
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

            $categories = $this->getCategoriesForGenre($genre);
        }
        // Get global explore page
        else {
            $categories = ExploreCategory::orderBy('position')->get();
        }

        return JSONResult::success([
            'data' => ExploreCategoryResource::collection(($categories))
        ]);
    }

    /**
     * Generates fixed explore page categories for a specific genre.
     *
     * @param Genre $genre
     * @return array
     */
    private function getCategoriesForGenre(Genre $genre): array
    {
        $categories = [];

        $categories[] = $this->getFeaturedShowsCategoryForGenre($genre, 1);
        $categories[] = $this->getShowsWeLoveCategoryForGenre($genre, 2);

        return $categories;
    }

    /**
     * Returns the explore category for featured shows for a specific genre.
     *
     * @param Genre $genre
     * @param int $position
     * @return ExploreCategory
     */
    private function getFeaturedShowsCategoryForGenre(Genre $genre, int $position): ExploreCategory
    {
        $exploreCategory = ExploreCategory::make([
            'title'     => 'Featured ' . $genre->name . ' Shows',
            'position'  => $position,
            'type'      => ExploreCategoryTypes::MostPopularShows,
            'size'      => 'large'
        ]);
        return $exploreCategory->most_popular_shows($genre);
    }

    /**
     * Returns the explore category for shows we love of a specific genre.
     *
     * @param Genre $genre
     * @param int $position
     * @return ExploreCategory
     */
    private function getShowsWeLoveCategoryForGenre(Genre $genre, int $position): ExploreCategory
    {
        $exploreCategory = ExploreCategory::make([
            'title'     => $genre->name . ' Shows We Love',
            'position'  => $position,
            'type'      => ExploreCategoryTypes::Shows,
            'size'      => 'video'
        ]);
        return $exploreCategory->shows_we_love($genre);
    }
}
