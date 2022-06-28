<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\GetCharacterAnimeRequest;
use App\Http\Requests\GetCharacterPeopleRequest;
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\CharacterResource;
use App\Http\Resources\PersonResourceIdentity;
use App\Models\Character;
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
     * @param GetCharacterPeopleRequest $request
     * @param Character $character
     * @return JsonResponse
     */
    public function people(GetCharacterPeopleRequest $request, Character $character): JsonResponse
    {
        $data = $request->validated();

        // Get the people
        $people = $character->getPeople($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $people->nextPageUrl());

        // Return character people
        return JSONResult::success([
            'data' => PersonResourceIdentity::collection($people),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns anime information about a character.
     *
     * @param GetCharacterAnimeRequest $request
     * @param Character $character
     * @return JsonResponse
     */
    public function anime(GetCharacterAnimeRequest $request, Character $character): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $anime = $character->getAnime($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $anime->nextPageUrl());

        // Return character anime
        return JSONResult::success([
            'data' => AnimeResourceIdentity::collection($anime),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
