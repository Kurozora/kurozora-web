<?php

namespace App\Http\Controllers\API\v1;

use App\Events\EpisodeViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetEpisodeReviewsRequest;
use App\Http\Requests\MarkEpisodeAsWatchedRequest;
use App\Http\Requests\RateEpisodeRequest;
use App\Http\Resources\EpisodeResource;
use App\Http\Resources\MediaRatingResource;
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
            throw new AuthorizationException(__('Please watch :x first.', ['x' => $episode->title]));
        }

        // Validate the request
        $data = $request->validated();

        // Fetch the variables
        $givenRating = $data['rating'];
        $description = $data['description'] ?? null;

        // Try to modify the rating if it already exists
        /** @var MediaRating $foundRating */
        $foundRating = $user->episodeRatings()
            ->where('model_id', '=', $episode->id)
            ->first();

        // The rating exists
        if ($foundRating) {
            // If the given rating is 0
            if ($givenRating <= 0) {
                // Delete the rating
                $foundRating->delete();
            } else {
                // Update the current rating
                $foundRating->update([
                    'rating'        => $givenRating,
                    'description'   => $description
                ]);
            }
        } else {
            // Only insert the rating if it's rated higher than 0
            if ($givenRating > 0) {
                MediaRating::create([
                    'user_id'       => $user->id,
                    'model_id'      => $episode->id,
                    'model_type'    => $episode->getMorphClass(),
                    'rating'        => $givenRating,
                    'description'   => $description,
                ]);
            }
        }

        return JSONResult::success();
    }

    /**
     * Returns the reviews of an Episode.
     *
     * @param GetEpisodeReviewsRequest $request
     * @param Episode $episode
     * @return JsonResponse
     */
    public function reviews(GetEpisodeReviewsRequest $request, Episode $episode): JsonResponse
    {
        $reviews = $episode->mediaRatings()
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $reviews->nextPageUrl());

        return JSONResult::success([
            'data' => MediaRatingResource::collection($reviews),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
