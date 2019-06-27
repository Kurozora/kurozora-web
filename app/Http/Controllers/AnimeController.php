<?php

namespace App\Http\Controllers;

use App\Anime;
use App\AnimeRating;
use App\Enums\AnimeStatus;
use App\Enums\AnimeType;
use App\Enums\UserLibraryStatus;
use App\Events\AnimeViewed;
use App\Helpers\JSONResult;
use App\Http\Resources\ActorResource;
use App\Http\Resources\AnimeSeasonResource;
use App\Http\Resources\GenreResource;
use App\UserLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AnimeController extends Controller
{
    /**
     * Returns detailed information about an Anime
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function detailsAnime(Anime $anime) {
        // Get the user rating for this Anime
        $userRating = 0.0;

        $foundRating = $anime->ratings()
            ->where('user_id', Auth::id())
            ->first();

        if($foundRating)
            $userRating = $foundRating->rating;

        // Get the current library status
        $currentLibraryStatus = null;

        $foundLibraryStatus = UserLibrary::where([
            ['user_id' ,  '=', Auth::id()],
            ['anime_id' , '=', $anime->id]
        ])->first();

        if($foundLibraryStatus)
            $currentLibraryStatus = UserLibraryStatus::getDescription($foundLibraryStatus->status);

        // Get the genres
        $genres = $anime->getGenres()->map(function($genre) {
            return GenreResource::make($genre);
        });

        // Build the response
        $animeArr = [
            'id'                    => $anime->id,
            'title'                 => $anime->title,
            'type'                  => AnimeType::getDescription($anime->type),
            'imdb_id'               => $anime->imdb_id,
            'network'               => $anime->network,
            'status'                => AnimeStatus::getDescription($anime->status),
            'episodes'              => $anime->episode_count,
            'seasons'               => $anime->season_count,
            'average_rating'        => $anime->average_rating,
            'rating_count'          => $anime->rating_count,
            'synopsis'              => $anime->synopsis,
            'runtime'               => $anime->runtime,
            'watch_rating'          => $anime->watch_rating,
            'poster'                => $anime->getPoster(false),
            'poster_thumbnail'      => $anime->getPoster(true),
            'background'            => $anime->getBackground(false),
            'background_thumbnail'  => $anime->getBackground(true),
            'nsfw'                  => (bool) $anime->nsfw,
            'genres'                => $genres
        ];

        $userArr = [
        	'current_rating'	=> $userRating,
            'library_status'    => $currentLibraryStatus
       	];

        // Call the AnimeViewed event
        AnimeViewed::dispatch($anime);

        // Show the Anime details response
        return JSONResult::success([
            'anime' => $animeArr,
            'user' => $userArr
        ]);
    }

    /**
     * Returns actor information about an Anime
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
     * Returns season information for an Anime
     *
     * @param Anime $anime
     * @return JsonResponse
     */
    public function seasonsAnime(Anime $anime)
    {
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
     * @param Anime $anime
     * @return JsonResponse
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

        // Format the results
        $displayResults = [];

        foreach($resultArr as $anime) {
            $displayResults[] = [
                'id'                => $anime->id,
                'title'             => $anime->title,
                'average_rating'    => $anime->average_rating,
                'poster_thumbnail'  => $anime->getPoster(true)
            ];
        }

        return JSONResult::success([
            'max_search_results'    => Anime::MAX_SEARCH_RESULTS,
            'results'               => $displayResults
        ]);
    }
}
