<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Helpers\JSONResult;
use App\Http\Requests\MarkEpisodeAsWatchedRequest;
use App\Http\Resources\EpisodeResource;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;

class EpisodeController extends Controller
{
    /**
     * Returns the information for an episode.
     *
     * @param Episode $episode
     * @return JsonResponse
     */
    public function details(Episode $episode): JsonResponse
    {
        return JSONResult::success([
            'data' => EpisodeResource::collection([$episode])
        ]);
    }

    /**
     * Marks an episode as watched or not watched.
     *
     * @param MarkEpisodeAsWatchedRequest $request
     * @param Episode $episode
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function watched(MarkEpisodeAsWatchedRequest $request, Episode $episode): JSONResponse
    {
        $user = Auth::user();

        // Find if the user has watched the episode
        $isAlreadyWatched = $user->hasWatched($episode);

        // If the episode's current status is watched then detach (unwatch) it, otherwise attach (watch) it.
        if ($isAlreadyWatched) {
            $user->watchedEpisodes()->detach($episode);
        } else {
            $user->watchedEpisodes()->attach($episode);
        }

        return JSONResult::success([
            'data' => [
                'isWatched' => !$isAlreadyWatched
            ]
        ]);
    }
}
