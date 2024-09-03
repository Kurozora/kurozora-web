<?php

namespace App\Http\Controllers\API\v1;

use App\Events\ModelViewed;
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
use Illuminate\Http\Request;

class EpisodeController extends Controller
{
    /**
     * Returns the information for an episode.
     *
     * @param Request $request
     * @param Episode $episode
     * @return JsonResponse
     */
    public function details(Request $request, Episode $episode): JsonResponse
    {
        // Call the ModelViewed event
        ModelViewed::dispatch($episode, $request->ip());

        $includeArray = [
            'media',
            'mediaStat',
            'translations',
            'videos',
        ];

        // Skeleton in case this logic ever changes.
        // For now both relations are already included,
        // so nothing else needs to be done.
        if ($includeInput = $request->input('include')) {
            if (is_string($includeInput)) {
                $includeInput = explode(',', $includeInput);
            }
            $includes = array_unique($includeInput);

            foreach ($includes as $include) {
                switch ($include) {
                    case 'show':
                        // Already included.
                    case 'season':
                        // Already included.
                        break;
                }
            }
        }

        $includeArray['anime'] = function ($query) {
            $query->withoutGlobalScopes()
                ->with(['media', 'translations']);
        };
        $includeArray['season'] = function ($query) {
            $query->with(['media']);
        };

        $episode->loadMissing($includeArray);

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
        $hasNotTracked = $user->hasNotTracked($episode->anime);

        if ($hasNotTracked) {
            // The item could not be found
            throw new AuthorizationException(__('Please add ":x" to your library first.', ['x' => $episode->anime->title]));
        }

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
            throw new AuthorizationException(__('Please watch ":x" first.', ['x' => $episode->title]));
        }

        // Validate the request
        $data = $request->validated();

        // Fetch the variables
        $givenRating = $data['rating'];
        $description = $data['description'] ?? null;

        // Modify the rating if it already exists
        /** @var MediaRating $foundRating */
        $foundRating = $user->episodeRatings()
            ->withoutTvRatings()
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
            ->withoutTvRatings()
            ->with([
                'user' => function ($query) {
                    $query->with(['media'])
                        ->withCount(['followers', 'followedModels as following_count', 'mediaRatings']);
                }
            ])
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $reviews->nextPageUrl());

        return JSONResult::success([
            'data' => MediaRatingResource::collection($reviews),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
