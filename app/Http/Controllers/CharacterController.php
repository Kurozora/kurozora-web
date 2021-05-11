<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Helpers\JSONResult;
use App\Http\Resources\PersonResource;
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
     * Returns person information about a character.
     *
     * @param Character $character
     * @return JsonResponse
     */
    public function people(Character $character): JsonResponse
    {
        // Get the people
        $people = $character->getPeople();

        // Return character people
        return JSONResult::success([
            'data' => PersonResource::collection($people)
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
