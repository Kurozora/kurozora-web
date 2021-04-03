<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Models\Actor;
use App\Http\Resources\ActorResource;
use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\CharacterResourceBasic;
use Illuminate\Http\JsonResponse;

class ActorController extends Controller
{
    /**
     * Shows actor details.
     *
     * @param Actor $actor
     * @return JsonResponse
     */
    public function details(Actor $actor): JsonResponse
    {
        // Return actor details
        return JSONResult::success([
            'data' => ActorResource::collection([$actor])
        ]);
    }

    /**
     * Returns anime information about an actor.
     *
     * @param Actor $actor
     * @return JsonResponse
     */
    public function anime(Actor $actor): JsonResponse
    {
        // Get the anime
        $anime = $actor->getAnime();

        // Return actor shows
        return JSONResult::success([
            'data' => AnimeResourceBasic::collection($anime)
        ]);
    }

    /**
     * Returns actor information about an actor.
     *
     * @param Actor $actor
     * @return JsonResponse
     */
    public function characters(Actor $actor): JsonResponse
    {
        // Get the actors
        $actors = $actor->getCharacters();

        // Return actor characters
        return JSONResult::success([
            'data' => CharacterResourceBasic::collection($actors)
        ]);
    }
}
