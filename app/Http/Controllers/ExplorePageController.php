<?php

namespace App\Http\Controllers;

use App\Enums\ExplorePageCategoryTypes;
use App\ExplorePageCategory;
use App\Genre;
use App\Helpers\JSONResult;
use App\Http\Requests\GetExplorePageRequest;
use App\Http\Resources\ExplorePageCategoryResource;
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
        if($request->has('genre_id')) {
            $genre = Genre::find($request->input('genre_id'));

            $categories = $this->getCategoriesForGenre($genre);
        }
        // Get global explore page
        else {
            $categories = ExplorePageCategory::orderBy('position')->get();
        }

        return JSONResult::success([
            'categories' => ExplorePageCategoryResource::collection(($categories))
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
     * Returns the EPC for featured shows for a specific genre.
     *
     * @param Genre $genre
     * @param int $position
     * @return ExplorePageCategory
     */
    private function getFeaturedShowsCategoryForGenre(Genre $genre, int $position): ExplorePageCategory
    {
        /** @var ExplorePageCategory $category */
        $category = ExplorePageCategory::make([
            'title'     => 'Featured ' . $genre->name . ' Shows',
            'position'  => $position,
            'type'      => ExplorePageCategoryTypes::Shows,
            'size'      => 'medium'
        ]);

        $popularShows = $genre->animes()->mostPopular(10)->get();

        foreach($popularShows as $popularShow)
            $category->animes->add($popularShow);

        return $category;
    }

    /**
     * Returns the EPC for shows we love of a specific genre.
     *
     * @param Genre $genre
     * @param int $position
     * @return ExplorePageCategory
     */
    private function getShowsWeLoveCategoryForGenre(Genre $genre, int $position): ExplorePageCategory
    {
        /** @var ExplorePageCategory $category */
        $category = ExplorePageCategory::make([
            'title'     => $genre->name . ' Shows We Love',
            'position'  => $position,
            'type'      => ExplorePageCategoryTypes::Shows,
            'size'      => 'medium'
        ]);

        $randomShows = $genre->animes()
            ->inRandomOrder()
            ->limit(10)
            ->get();

        foreach($randomShows as $randomShow)
            $category->animes->add($randomShow);

        return $category;
    }
}
