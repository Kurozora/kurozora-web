<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Helpers\JSONResult;
use App\Http\Requests\MarkEpisodeAsWatchedRequest;
use App\Http\Resources\EpisodeResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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

        // Attach or detach the watched episode
        if ($isAlreadyWatched) // Unwatch the episode
            $user->watchedEpisodes()->detach($episode);
        else // Watch the episode
            $user->watchedEpisodes()->attach($episode);

        return JSONResult::success([
            'data' => [
                'isWatched' => !$isAlreadyWatched
            ]
        ]);
    }
}
