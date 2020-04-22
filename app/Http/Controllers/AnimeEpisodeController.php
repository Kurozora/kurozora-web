<?php

namespace App\Http\Controllers;

use App\AnimeEpisode;
use App\Enums\WatchStatus;
use App\Helpers\JSONResult;
use App\Http\Requests\MarkEpisodeAsWatchedRequest;
use App\UserWatchedEpisode;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AnimeEpisodeController extends Controller
{
    /**
     * Marks an episode as watched or not watched.
     *
     * @param MarkEpisodeAsWatchedRequest $request
     * @param AnimeEpisode $episode
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function watched(MarkEpisodeAsWatchedRequest $request, AnimeEpisode $episode)
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
            'watched' => $watched
        ]);
    }
}
