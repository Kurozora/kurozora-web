<?php

namespace App\Http\Controllers\API\v1;

use App\Events\EpisodeViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\MarkEpisodeAsWatchedRequest;
use App\Http\Requests\RateEpisodeRequest;
use App\Http\Resources\EpisodeResource;
use App\Models\Episode;
use App\Models\MediaRating;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
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
        // Call the EpisodeViewed event
        EpisodeViewed::dispatch($episode);

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
        $user = auth()->user();

        // Find if the user has watched the episode
        $isAlreadyWatched = $user->hasWatched($episode);

        // If the episode's current status is watched then detach (unwatch) it, otherwise attach (watch) it.
        if ($isAlreadyWatched) {
            $user->episodes()->detach($episode);
        } else {
            $user->episodes()->attach($episode);
        }

        return JSONResult::success([
            'data' => [
                'isWatched' => !$isAlreadyWatched
            ]
        ]);
    }

    /**
     * Adds a rating for an Anime item
     *
     * @param RateEpisodeRequest $request
     * @param Episode $episode
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function rateEpisode(RateEpisodeRequest $request, Episode $episode): JsonResponse
    {
        $user = auth()->user();

        // Check if the episode has been watched
        if (!$user->hasWatched($episode)) {
            throw new AuthorizationException('Please watch ' . $episode->title . ' first.');
        }

        // Validate the request
        $data = $request->validated();

        // Fetch the variables
        $givenRating = $data['rating'];

        // Try to modify the rating if it already exists
        /** @var MediaRating $foundRating */
        $foundRating = $user->episode_ratings()->where([
            ['model_id', '=', $episode->id],
            ['model_type', '=', Episode::class],
        ])->first();

        // The rating exists
        if ($foundRating) {
            // If the given rating is 0
            if ($givenRating <= 0) {
                // Delete the rating
                $foundRating->delete();
            } else {
                // Update the current rating
                $foundRating->update([
                    'rating' => $givenRating
                ]);
            }
        } else {
            // Only insert the rating if it's rated higher than 0
            if ($givenRating > 0) {
                $user->episode_ratings()->create([
                    'user_id' => $user->id,
                    'model_type' => Episode::class,
                    'model_id' => $episode->id,
                    'rating' => $givenRating,
                ]);
            }
        }

        return JSONResult::success();
    }
}
