<?php

namespace App\Http\Controllers\API\v1;

use App\Events\CharacterViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetCharacterAnimeRequest;
use App\Http\Requests\GetCharacterGameRequest;
use App\Http\Requests\GetCharacterLiteratureRequest;
use App\Http\Requests\GetCharacterPeopleRequest;
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\CharacterResource;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\LiteratureResourceIdentity;
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
        // Call the CharacterViewed event
        CharacterViewed::dispatch($character);

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

    /**
     * Returns literatures information about a character.
     *
     * @param GetCharacterLiteratureRequest $request
     * @param Character $character
     * @return JsonResponse
     */
    public function literatures(GetCharacterLiteratureRequest $request, Character $character): JsonResponse
    {
        $data = $request->validated();

        // Get the literatures
        $literatures = $character->getManga($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $literatures->nextPageUrl());

        // Return character literatures
        return JSONResult::success([
            'data' => LiteratureResourceIdentity::collection($literatures),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns games information about a character.
     *
     * @param GetCharacterGameRequest $request
     * @param Character $character
     * @return JsonResponse
     */
    public function games(GetCharacterGameRequest $request, Character $character): JsonResponse
    {
        $data = $request->validated();

        // Get the games
        $games = $character->getGames($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $games->nextPageUrl());

        // Return character games
        return JSONResult::success([
            'data' => GameResourceIdentity::collection($games),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
