<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRequest;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;

class GenreController extends Controller
{
    /**
     * Generate an overview of genres.
     *
     * @param GetIndexRequest $request
     *
     * @return JsonResponse
     */
    public function index(GetIndexRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['ids'])) {
            return $this->views($request);
        } else {
            // Get all genres
            $genres = Genre::orderBy('name')
                ->with(['media'])
                ->get();

            // Show genres in response
            return JSONResult::success([
                'data' => GenreResource::collection($genres)
            ]);
        }
    }

    /**
     * Returns detailed information of requested IDs.
     *
     * @param GetIndexRequest $request
     *
     * @return JsonResponse
     */
    public function views(GetIndexRequest $request): JsonResponse
    {
        $data = $request->validated();

        $genre = Genre::whereIn('id', $data['ids'] ?? []);
        $genre->with(['media']);

        // Show the genre details response
        return JSONResult::success([
            'data' => GenreResource::collection($genre->get()),
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
        $genre->load(['media']);

        // Show genre details
        return JSONResult::success([
            'data' => GenreResource::collection([$genre])
        ]);
    }
}
