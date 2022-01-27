<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;

class GenreController extends Controller
{
    /**
     * Generate an overview of genres.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Get all genres and format them
        $genres = Genre::all();

        // Show genres in response
        return JSONResult::success([
            'data' => GenreResource::collection($genres)
        ]);
    }

    /**
     * Shows genre details
     *
     * @param Genre $genre
     * @return JsonResponse
     */
    public function details(Genre $genre): JsonResponse
    {
        // Show genre details
        return JSONResult::success([
            'data' => GenreResource::collection([$genre])
        ]);
    }
}
