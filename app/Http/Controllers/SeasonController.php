<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\GetSeasonEpisodesRequest;
use App\Http\Resources\EpisodeResource;
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
            'data' => EpisodeResource::collection($episodes),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
