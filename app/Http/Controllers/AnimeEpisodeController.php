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
        // Fetch the variables
        $watchedInt = (int) $request->input('watched');

        // Find if the user has watched the episode
        $foundWatched = UserWatchedEpisode::where([
            ['user_id', '=', Auth::id()],
            ['episode_id', '=', $episode->id]
        ])->first();

        // User wants to mark as "watched" and hasn't already watched it
        if ($watchedInt == WatchStatus::WATCHED()->value && !$foundWatched) {
            UserWatchedEpisode::create([
                'user_id' => Auth::id(),
                'episode_id' => $episode->id
            ]);
        } // User wants to mark as "not watched" and has already watched it
        else if ($watchedInt == WatchStatus::NOT_WATCHED()->value && $foundWatched) {
            $foundWatched->delete();
        }

        return JSONResult::success([
            'watched' => $watchedInt
        ]);
    }
}
