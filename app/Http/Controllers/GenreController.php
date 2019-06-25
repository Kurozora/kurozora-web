<?php

namespace App\Http\Controllers;

use App\Genre;
use App\Helpers\JSONResult;
use App\Http\Resources\GenreResource;

class GenreController extends Controller
{
    /**
     * Generate an overview of genres
     */
    public function overview() {
        // Get all genres and format them
        $allGenres = Genre::get()->map(function($genre) {
            return GenreResource::make($genre);
        });

        // Show genres in response
        (new JSONResult())->setData(['genres' => $allGenres])->show();
    }

    /**
     * Shows genre details
     *
     * @param Genre $genre
     */
    public function details(Genre $genre) {
        // Show genre details
        (new JSONResult())->setData([
            'genre' => GenreResource::make($genre)
        ])->show();
    }
}
