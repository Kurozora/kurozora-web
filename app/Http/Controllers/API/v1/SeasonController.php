<?php

namespace App\Http\Controllers\API\v1;

use App\Events\SeasonViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetSeasonEpisodesRequest;
use App\Http\Requests\MarkSeasonAsWatchedRequest;
use App\Http\Resources\EpisodeResourceIdentity;
use App\Http\Resources\SeasonResource;
use App\Models\Season;
use Illuminate\Http\JsonResponse;

class SeasonController extends Controller
{
    /**
     * Returns the information for a season
     *
     * @param Season $season
     * @return JsonResponse
     */
    public function details(Season $season): JsonResponse
    {
        // Call the SeasonViewed event
        SeasonViewed::dispatch($season);

        $season->load(['anime', 'media'])
            ->loadCount(['episodes']);

        return JSONResult::success([
            'data' => SeasonResource::collection([$season])
        ]);
    }

    /**
     * Returns the episodes for a season
     *
     * @param GetSeasonEpisodesRequest $request
     * @param Season $season
     * @return JsonResponse
     */
    public function episodes(GetSeasonEpisodesRequest $request, Season $season): JsonResponse
    {
        $data = $request->validated();

        // Get the episodes
        $episodes = $season->episodes();

        // Fillers
        if ($data['hide_fillers'] ?? false) {
            $episodes = $episodes->where('is_filler', '!=', $data['hide_fillers']);
        }

        // Paginate
        $episodes = $episodes->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $episodes->nextPageUrl());

        return JSONResult::success([
            'data' => EpisodeResourceIdentity::collection($episodes),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }


    /**
     * Marks an episode as watched or not watched.
     *
     * @param MarkSeasonAsWatchedRequest $request
     * @param Season $season
     *
     * @return JsonResponse
     */
    public function watched(MarkSeasonAsWatchedRequest $request, Season $season): JSONResponse
    {
        $user = auth()->user();
        $episodeIDs = $season->episodes()->pluck('id');

        // Find if the user has watched the season
        $isAlreadyWatched = $user->hasWatchedSeason($season);

        // If the episode's current status is watched then detach (unwatch) it, otherwise attach (watch) it.
        if ($isAlreadyWatched) {
            $user->episodes()->detach($episodeIDs);
        } else {
            $existingIDs = $user->episodes()
                ->whereIn('episode_id', $episodeIDs)
                ->pluck('episode_id');
            $diffedEpisodeIDs = $episodeIDs->diff($existingIDs);

            $user->episodes()->attach($diffedEpisodeIDs);
        }

        return JSONResult::success([
            'data' => [
                'isWatched' => !$isAlreadyWatched
            ]
        ]);
    }
}
