<?php

namespace App\Http\Controllers;

use App\Genre;
use App\Helpers\JSONResult;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    /**
     * Generate an overview of genres
     */
    public function overview() {
        // Get all genres and format them
        $allGenres = Genre::get()->map(function($genre) {
            return $genre->formatForOverviewResponse();
        });

        // Show genres in response
        (new JSONResult())->setData(['genres' => $allGenres])->show();
    }
}
