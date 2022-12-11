<?php

namespace App\Http\Controllers\API\v1;

use App\Events\SongViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetSongAnimesRequest;
use App\Http\Resources\AnimeResource;
use App\Http\Resources\SongResource;
use App\Models\Song;
use Illuminate\Http\JsonResponse;

class SongController extends Controller
{
    /**
     * Shows song details.
     *
     * @param Song $song
     * @return JsonResponse
     */
    public function details(Song $song): JsonResponse
    {
        // Call the SongViewed event
        SongViewed::dispatch($song);

        return JSONResult::success([
            'data' => SongResource::collection([$song])
        ]);
    }

    /**
     * Returns anime information for a Song
     *
     * @param GetSongAnimesRequest $request
     * @param Song $song
     * @return JsonResponse
     */
    public function anime(GetSongAnimesRequest $request, Song $song): JsonResponse
    {
        $data = $request->validated();

        // Get the seasons
        $animes = $song->getAnime($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $animes->nextPageUrl());

        return JSONResult::success([
            'data' => AnimeResource::collection($animes),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
