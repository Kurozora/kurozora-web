<?php

namespace App\Http\Controllers;

use App\AnimeEpisode;
use App\Helpers\JSONResult;
use App\UserWatchedEpisode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnimeEpisodeController extends Controller
{
    /**
     * Marks an episode as watched or not watched
     *
     * @param $episodeID
     */
    public function watched(Request $request, $episodeID) {
        // Get the episode
        $episode = AnimeEpisode::find($episodeID);

        // Episode does not exist
        if(!$episode)
            (new JSONResult())->setError(JSONResult::ERROR_ANIME_EPISODE_NON_EXISTENT)->show();

        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'watched' => 'bail|required|numeric|min:0|max:1'
        ]);

        // Check validator
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        // Fetch the variables
        $watchedBool = (bool) $request->input('watched');

        // Find if the user has watched the episode
        $foundWatched = UserWatchedEpisode::where([
            ['user_id',     '=', $request->user_id],
            ['episode_id',  '=', $episode->id]
        ])->first();

        // User wants to mark as "watched" and hasn't already watched it
        if($watchedBool && !$foundWatched) {
            UserWatchedEpisode::create([
                'user_id'       => $request->user_id,
                'episode_id'    => $episode->id
            ]);
        }
        // User wants to mark as "not watched" and has already watched it
        else if(!$watchedBool && $foundWatched) {
            $foundWatched->delete();
        }

        (new JSONResult())->setData([
            'watched' => $watchedBool
        ])->show();
    }
}
