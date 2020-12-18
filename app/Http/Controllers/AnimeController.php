<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\AnimeRating;
use App\Events\AnimeViewed;
use App\Helpers\JSONResult;
use App\Http\Requests\RateAnimeRequest;
use App\Http\Requests\SearchAnimeRequest;
use App\Http\Resources\ActorCharacterAnimeResource;
use App\Http\Resources\ActorResource;
use App\Http\Resources\AnimeRelatedShowsResource;
use App\Http\Resources\AnimeResource;
use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\AnimeSeasonResource;
use App\Http\Resources\CharacterResourceBasic;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AnimeController extends Controller
{
    /**
     * Returns detailed information about an Anime.
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function view(Anime $anime): JsonResponse
    {
        // Call the AnimeViewed event
        AnimeViewed::dispatch($anime);

        // Show the Anime details response
        return JSONResult::success([
            'data' => AnimeResource::collection([$anime])
        ]);
    }

    /**
     * Returns actor information about an Anime.
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function actorsAnime(Anime $anime): JsonResponse
    {
        // Get the actors
        $actors = $anime->getActors();

        return JSONResult::success([
            'data' => ActorResource::collection($actors)
        ]);
    }

    /**
     * Returns character information about an Anime.
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function charactersAnime(Anime $anime): JsonResponse
    {
        // Get the actors
        $actors = $anime->getCharacters();

        return JSONResult::success([
            'data' => CharacterResourceBasic::collection($actors)
        ]);
    }

    /**
     * Returns actor-character-anime information about an Anime.
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function actorCharacterAnime(Anime $anime): JsonResponse
    {
        // Get the actor-character-anime
        $actorCharacterAnime = $anime->getActorCharacterAnime();

        return JSONResult::success([
            'data' => ActorCharacterAnimeResource::collection($actorCharacterAnime)
        ]);
    }

    /**
     * Returns related-shows information about an Anime.
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function relatedShowsAnime(Anime $anime): JsonResponse
    {
        // Get the related shows
        $relations = $anime->getAnimeRelations();

        return JSONResult::success([
            'data' => AnimeRelatedShowsResource::collection($relations)
        ]);
    }

    /**
     * Returns season information for an Anime
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function seasonsAnime(Anime $anime): JsonResponse
    {
        // Get the seasons
        $seasons = $anime->getSeasons();

        return JSONResult::success([
            'data' => AnimeSeasonResource::collection($seasons)
        ]);
    }

    /**
     * Adds a rating for an Anime item
     *
     * @param RateAnimeRequest $request
     * @param Anime $anime
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function rateAnime(RateAnimeRequest $request, Anime $anime): JsonResponse
    {
        if (!Auth::user()->isTracking($anime))
            throw new AuthorizationException('Please add ' . $anime->title . ' to your library first.');

        // Fetch the variables
        $givenRating = $request->input('rating');

        // Try to modify the rating if it already exists
        /** @var AnimeRating $foundRating */
        $foundRating = AnimeRating::where([
            ['anime_id', '=', $anime->id],
            ['user_id', '=', Auth::id()]
        ])->first();

        // The rating exists
        if($foundRating) {
            // If the given rating is 0, delete the rating
            if($givenRating <= 0)
                $foundRating->delete();
            // Update the current rating
            else {
                $foundRating->rating = $givenRating;
                $foundRating->save();
            }
        }
        // Rating needs to be inserted
        else {
            // Only insert the rating if it's rated higher than 0
            if($givenRating > 0) {
                AnimeRating::create([
                    'anime_id'  => $anime->id,
                    'user_id'   => Auth::id(),
                    'rating'    => $givenRating
                ]);
            }
        }

        return JSONResult::success();
    }

    /**
     * Retrieves Anime search results
     *
     * @param SearchAnimeRequest $request
     * @return JsonResponse
     */
    public function search(SearchAnimeRequest $request): JsonResponse
    {
        $searchQuery = $request->input('query');

        // Search for the Anime
        $resultArr = Anime::kuroSearch($searchQuery, [
            'limit' => Anime::MAX_SEARCH_RESULTS
        ]);

        return JSONResult::success([
            'data' => AnimeResourceBasic::collection($resultArr)
        ]);
    }
}
