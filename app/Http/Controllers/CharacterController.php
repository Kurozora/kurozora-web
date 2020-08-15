<?php

namespace App\Http\Controllers;

use App\Character;
use App\Helpers\JSONResult;
use App\Http\Resources\ActorResource;
use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\CharacterResource;
use Illuminate\Http\JsonResponse;

class CharacterController extends Controller
{
    /**
     * Generate an overview of characters.
     *
     * @return JsonResponse
     */
    public function overview(): JsonResponse
    {
        // Get all characters and format them
        $allCharacters = Character::get()->map(function($character) {
            return CharacterResource::make($character);
        });

        // Show characters in response
        return JSONResult::success(['data' => $allCharacters]);
    }

    /**
     * Shows character details.
     *
     * @param Character $character
     * @return JsonResponse
     */
    public function details(Character $character): JsonResponse
    {
        // Show character details
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

        // Show character details
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

        // Show character details
        return JSONResult::success([
            'data' => AnimeResourceBasic::collection($anime)
        ]);
    }
}
