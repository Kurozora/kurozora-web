<?php

namespace App\Http\Controllers;

use App\Anime;
use App\AnimeRating;
use App\Events\AnimeViewed;
use App\Helpers\JSONResult;
use App\Http\Resources\ActorResource;
use App\Http\Resources\AnimeResource;
use App\Http\Resources\AnimeSeasonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AnimeController extends Controller
{
    /**
     * Returns detailed information about an Anime.
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function view(Anime $anime) {
        // Call the AnimeViewed event
        AnimeViewed::dispatch($anime);

        // Show the Anime details response
        return JSONResult::success([
            'anime' => AnimeResource::make($anime)
        ]);
    }

    /**
     * Returns actor information about an Anime.
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function actorsAnime(Anime $anime) {
        // Get the actors
        $actors = $anime->getActors();

        return JSONResult::success([
            'total_actors'  => count($actors),
            'actors'        => ActorResource::collection($actors)
        ]);
    }

    /**
     * Returns relations information about an Anime.
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function relationsAnime(Anime $anime) {
        // Get the actors
        $relations = $anime->getRelatedAnime();

        return JSONResult::success([
            'related' => [
                'shows' => AnimeResource::collection($relations)
            ]
        ]);
    }

    /**
     * Returns season information for an Anime
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function seasonsAnime(Anime $anime) {
        // Get the seasons
        $seasons = $anime->getSeasons();

        return JSONResult::success([
            'seasons' => AnimeSeasonResource::collection($seasons)
        ]);
    }

    /**
     * Adds a rating for an Anime item
     *
     * @param Request $request
     * @param Anime   $anime
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function rateAnime(Request $request, Anime $anime) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'rating' => 'bail|required|numeric|between:' . AnimeRating::MIN_RATING_VALUE . ',' . AnimeRating::MAX_RATING_VALUE
        ]);

        // Check validator
        if ($validator->fails())
            return JSONResult::error($validator->errors()->first());

        // Fetch the variables
        $givenRating = $request->input('rating');

        // Try to modify the rating if it already exists
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
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'query' => 'bail|required|string|min:1'
        ]);

        // Check validator
        if($validator->fails())
            return JSONResult::error($validator->errors()->first());

        $searchQuery = $request->input('query');

        // Search for the Anime
        $resultArr = Anime::kuroSearch($searchQuery, [
            'limit' => Anime::MAX_SEARCH_RESULTS
        ]);

        return JSONResult::success([
            'max_search_results'    => Anime::MAX_SEARCH_RESULTS,
            'results'               => AnimeResource::collection($resultArr)
        ]);
    }
}
