<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ExploreCategory;
use App\Models\Genre;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class GenreController extends Controller
{
    /**
     * Show the list of genres.
     *
     * @return Application|Factory|View
     */
    public function index(): Application|Factory|View
    {
        return view('genre.index', [
            'genres' => Genre::orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Show a genre's page.
     *
     * @param Genre $genre
     *
     * @return Application|Factory|View
     */
    public function show(Genre $genre): Application|Factory|View
    {
        $genre->load(['media']);

        $exploreCategories = ExploreCategory::where('is_global', true)
            ->orderBy('position')
            ->get();

        return view('genre.details', [
            'genre' => $genre,
            'exploreCategories' => $exploreCategories,
            'exploreCategoryItems' => ExploreCategory::itemsFor($exploreCategories, $genre),
        ]);
    }
}
