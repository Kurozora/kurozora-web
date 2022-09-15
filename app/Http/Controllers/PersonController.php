<?php

namespace App\Http\Controllers;

use App\Events\PersonViewed;
use App\Helpers\JSONResult;
use App\Http\Requests\GetPersonAnimeRequest;
use App\Http\Requests\GetPersonCharactersRequest;
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\PersonResource;
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
        // Call the PersonViewed event
        PersonViewed::dispatch($person);

        // Return person details
        return JSONResult::success([
            'data' => PersonResource::collection([$person])
        ]);
    }

    /**
     * Returns anime information of the person.
     *
     * @param GetPersonAnimeRequest $request
     * @param Person $person
     * @return JsonResponse
     */
    public function anime(GetPersonAnimeRequest $request, Person $person): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $anime = $person->getAnime($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $anime->nextPageUrl());

        // Return character anime
        return JSONResult::success([
            'data' => AnimeResourceIdentity::collection($anime),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns character information of the person.
     *
     * @param GetPersonCharactersRequest $request
     * @param Person $person
     * @return JsonResponse
     */
    public function characters(GetPersonCharactersRequest $request, Person $person): JsonResponse
    {
        $data = $request->validated();

        // Get the characters
        $characters = $person->getCharacters($data['limit'] ?? 25, $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $characters->nextPageUrl());

        // Return person characters
        return JSONResult::success([
            'data' => CharacterResourceIdentity::collection($characters),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
