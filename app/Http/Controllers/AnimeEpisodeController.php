<?php

namespace App\Http\Controllers;

use App\AnimeEpisode;
use App\Enums\WatchStatus;
use App\Helpers\JSONResult;
use App\Http\Requests\MarkEpisodeAsWatchedRequest;
use App\Http\Resources\AnimeEpisodeResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AnimeEpisodeController extends Controller
{
    /**
     * Returns the information for an episode.
     *
     * @param Request $request
     * @param AnimeEpisode $episode
     * @return JsonResponse
     */
    public function details(Request $request, AnimeEpisode $episode): JsonResponse
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
        $watched = (int) $request->input('watched');

        // Find if the user has watched the episode
        $alreadyWatched = $user->hasWatched($episode);

        // Attach or detach the watched episode
        if ($watched == WatchStatus::Watched && !$alreadyWatched)
        {
            $user->watchedAnimeEpisodes()->attach($episode);
        }
        else if ($watched == WatchStatus::NotWatched && $alreadyWatched)
        {
            $user->watchedAnimeEpisodes()->detach($episode);
        }

        return JSONResult::success([
            'data' => [
                'watched' => $watched
            ]
        ]);
    }
}
