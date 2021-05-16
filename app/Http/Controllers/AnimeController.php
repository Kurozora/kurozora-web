<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnimeStaffResource;
use App\Http\Resources\StudioResource;
use App\Models\Anime;
use App\Models\AnimeRating;
use App\Events\AnimeViewed;
use App\Helpers\JSONResult;
use App\Http\Requests\RateAnimeRequest;
use App\Http\Requests\SearchAnimeRequest;
use App\Http\Resources\AnimeCastResource;
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
     * Returns detailed information of an Anime.
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
     * Returns character information of an Anime.
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function characters(Anime $anime): JsonResponse
    {
        // Get the characters
        $characters = $anime->getCharacters();

        return JSONResult::success([
            'data' => CharacterResourceBasic::collection($characters)
        ]);
    }

    /**
     * Returns the cast information of an Anime.
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function cast(Anime $anime): JsonResponse
    {
        // Get the anime cast
        $animeCast = $anime->getCast();

        return JSONResult::success([
            'data' => AnimeCastResource::collection($animeCast)
        ]);
    }

    /**
     * Returns related-shows information of an Anime.
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function relatedShows(Anime $anime): JsonResponse
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
    public function seasons(Anime $anime): JsonResponse
    {
        // Get the seasons
        $seasons = $anime->getSeasons();

        return JSONResult::success([
            'data' => AnimeSeasonResource::collection($seasons)
        ]);
    }

    /**
     * Returns staff information of an Anime.
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function staff(Anime $anime): JsonResponse
    {
        // Get the staff
        $staff = $anime->getStaff();

        return JSONResult::success([
            'data' => AnimeStaffResource::collection($staff)
        ]);
    }

    /**
     * Returns the studios information of an Anime.
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function studios(Anime $anime): JsonResponse
    {
        // Get the anime studios
        $animeStudios = $anime->getStudios();

        return JSONResult::success([
            'data' => StudioResource::collection($animeStudios)
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
        if ($foundRating) {
            // If the given rating is 0, delete the rating
            if ($givenRating <= 0)
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
            if ($givenRating > 0) {
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
        $resultArr = Anime::kSearch($searchQuery, [
            'limit' => Anime::MAX_SEARCH_RESULTS
        ]);

        return JSONResult::success([
            'data' => AnimeResourceBasic::collection($resultArr)
        ]);
    }
}
