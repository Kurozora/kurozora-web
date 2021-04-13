<?php

namespace App\Http\Controllers;

use App\Models\AnimeEpisode;
use App\Helpers\JSONResult;
use App\Http\Requests\MarkEpisodeAsWatchedRequest;
use App\Http\Resources\AnimeEpisodeResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AnimeEpisodeController extends Controller
{
    /**
     * Returns the information for an episode.
     *
     * @param AnimeEpisode $episode
     * @return JsonResponse
     */
    public function details(AnimeEpisode $episode): JsonResponse
    {
        return JSONResult::success([
            'data' => AnimeEpisodeResource::collection([$episode])
        ]);
    }

    /**
     * Marks an episode as watched or not watched.
     *
     * @param MarkEpisodeAsWatchedRequest $request
     * @param AnimeEpisode $episode
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function watched(MarkEpisodeAsWatchedRequest $request, AnimeEpisode $episode): JSONResponse
    {
        $user = Auth::user();

        // Find if the user has watched the episode
        $isAlreadyWatched = $user->hasWatched($episode);

        // Attach or detach the watched episode
        if ($isAlreadyWatched) // Unwatch the episode
            $user->watchedAnimeEpisodes()->detach($episode);
        else // Watch the episode
            $user->watchedAnimeEpisodes()->attach($episode);

        return JSONResult::success([
            'data' => [
                'isWatched' => !$isAlreadyWatched
            ]
        ]);
    }
}
