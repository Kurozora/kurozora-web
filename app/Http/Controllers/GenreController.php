<?php

namespace App\Http\Controllers;

use App\Genre;
use App\Helpers\JSONResult;
use App\Http\Resources\GenreResource;
use Illuminate\Http\JsonResponse;

class GenreController extends Controller
{
    /**
     * Generate an overview of genres.
     *
     * @return JsonResponse
     */
    public function overview() {
        // Get all genres and format them
        $allGenres = Genre::get()->map(function($genre) {
            return GenreResource::make($genre);
        });

        // Show genres in response
        return JSONResult::success(['data' => $allGenres]);
    }

    /**
     * Shows genre details
     *
     * @param Genre $genre
     * @return JsonResponse
     */
    public function details(Genre $genre) {
        // Show genre details
        return JSONResult::success([
            'data' => GenreResource::collection([$genre])
        ]);
    }
}
