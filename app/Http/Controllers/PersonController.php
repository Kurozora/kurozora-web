<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Resources\PersonResource;
use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\CharacterResourceBasic;
use App\Models\Person;
use Illuminate\Http\JsonResponse;

class PersonController extends Controller
{
    /**
     * Shows person details.
     *
     * @param Person $person
     * @return JsonResponse
     */
    public function details(Person $person): JsonResponse
    {
        // Return person details
        return JSONResult::success([
            'data' => PersonResource::collection([$person])
        ]);
    }

    /**
     * Returns anime information of the person.
     *
     * @param Person $person
     * @return JsonResponse
     */
    public function anime(Person $person): JsonResponse
    {
        // Get the anime
        $anime = $person->getAnime();

        // Return person shows
        return JSONResult::success([
            'data' => AnimeResourceBasic::collection($anime)
        ]);
    }

    /**
     * Returns character information of the person.
     *
     * @param Person $person
     * @return JsonResponse
     */
    public function characters(Person $person): JsonResponse
    {
        // Get the characters
        $characters = $person->getCharacters();

        // Return person characters
        return JSONResult::success([
            'data' => CharacterResourceBasic::collection($characters)
        ]);
    }
}
