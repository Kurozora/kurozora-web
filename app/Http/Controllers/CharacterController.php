<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Helpers\JSONResult;
use App\Http\Resources\ActorResource;
use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\CharacterResource;
use Illuminate\Http\JsonResponse;

class CharacterController extends Controller
{
    /**
     * Shows character details.
     *
     * @param Character $character
     * @return JsonResponse
     */
    public function details(Character $character): JsonResponse
    {
        // Return character details
        return JSONResult::success([
            'data' => CharacterResource::collection([$character])
        ]);
    }

    /**
     * Returns actor information about a character.
     *
     * @param Character $character
     * @return JsonResponse
     */
    public function actors(Character $character): JsonResponse
    {
        // Get the actors
        $actors = $character->getActors();

        // Return character actors
        return JSONResult::success([
            'data' => ActorResource::collection($actors)
        ]);
    }

    /**
     * Returns anime information about a character.
     *
     * @param Character $character
     * @return JsonResponse
     */
    public function anime(Character $character): JsonResponse
    {
        // Get the anime
        $anime = $character->getAnime();

        // Return character anime
        return JSONResult::success([
            'data' => AnimeResourceBasic::collection($anime)
        ]);
    }
}
