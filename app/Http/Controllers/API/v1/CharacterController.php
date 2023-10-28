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
use Illuminate\Http\Request;

class CharacterController extends Controller
{
    /**
     * Shows character details.
     *
     * @param Request $request
     * @param Character $character
     * @return JsonResponse
     */
    public function details(Request $request, Character $character): JsonResponse
    {
        // Call the CharacterViewed event
        CharacterViewed::dispatch($character);

        $character->load(['media', 'translations']);

        $includeArray = [];
        if ($includeInput = $request->input('include')) {
            if (is_string($includeInput)) {
                $includeInput = explode(',', $includeInput);
            }
            $includes = array_unique($includeInput);

            foreach ($includes as $include) {
                switch ($include) {
                    case 'people':
                        $includeArray['people'] = function ($query) {
                            $query->with(['media'])
                                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'shows':
                        $includeArray['anime'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
                                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'literatures':
                        $includeArray['manga'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
                                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'games':
                        $includeArray['games'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
                                ->limit(Character::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                }
            }
        }
        $character->loadMissing($includeArray);

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
        $people = $character->people()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

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
        $anime = $character->anime()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

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
        $literatures = $character->manga()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

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
        $games = $character->games()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $games->nextPageUrl());

        // Return character games
        return JSONResult::success([
            'data' => GameResourceIdentity::collection($games),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
