<?php

namespace App\Http\Controllers\API\v1;

use App\Events\PersonViewed;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetPersonAnimeRequest;
use App\Http\Requests\GetPersonCharactersRequest;
use App\Http\Requests\GetPersonGameRequest;
use App\Http\Requests\GetPersonLiteratureRequest;
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\PersonResource;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    /**
     * Shows person details.
     *
     * @param Request $request
     * @param Person $person
     * @return JsonResponse
     */
    public function details(Request $request, Person $person): JsonResponse
    {
        // Call the PersonViewed event
        PersonViewed::dispatch($person);

        $person->load(['media']);

        $includeArray = [];
        if ($includeInput = $request->input('include')) {
            $includes = array_unique(explode(',', $includeInput));

            foreach ($includes as $include) {
                switch ($include) {
                    case 'characters':
                        $includeArray['characters'] = function ($query) {
                            $query->with(['media', 'translations'])
                                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'shows':
                        $includeArray['anime'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
                                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'literatures':
                        $includeArray['manga'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
                                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                    case 'games':
                        $includeArray['games'] = function ($query) {
                            $query->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating'])
                                ->limit(Person::MAXIMUM_RELATIONSHIPS_LIMIT);
                        };
                        break;
                }
            }
        }
        $person->loadMissing($includeArray);

        // Return person details
        return JSONResult::success([
            'data' => PersonResource::collection([$person])
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
        $characters = $person->characters()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $characters->nextPageUrl());

        // Return person characters
        return JSONResult::success([
            'data' => CharacterResourceIdentity::collection($characters),
            'next' => empty($nextPageURL) ? null : $nextPageURL
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
        $anime = $person->anime()
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
     * Returns literatures information of the person.
     *
     * @param GetPersonLiteratureRequest $request
     * @param Person $person
     * @return JsonResponse
     */
    public function literatures(GetPersonLiteratureRequest $request, Person $person): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $literature = $person->manga()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $literature->nextPageUrl());

        // Return character literature
        return JSONResult::success([
            'data' => LiteratureResourceIdentity::collection($literature),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Returns games information of the person.
     *
     * @param GetPersonGameRequest $request
     * @param Person $person
     * @return JsonResponse
     */
    public function games(GetPersonGameRequest $request, Person $person): JsonResponse
    {
        $data = $request->validated();

        // Get the anime
        $game = $person->games()
            ->paginate($data['limit'] ?? 25, page: $data['page'] ?? 1);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $game->nextPageUrl());

        // Return character game
        return JSONResult::success([
            'data' => GameResourceIdentity::collection($game),
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }
}
