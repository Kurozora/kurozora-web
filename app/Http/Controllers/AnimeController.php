<?php

namespace App\Http\Controllers;

use App\Actor;
use App\Anime;
use App\AnimeRating;
use App\Helpers\JSONResult;
use App\UserLibrary;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AnimeController extends Controller
{
    /**
     * Returns the necessary data for the Anime explore page
     */
    public function exploreAnime() {
        // Retrieve or save cached result
        $explorePage = Cache::remember(Anime::CACHE_KEY_EXPLORE, Anime::CACHE_KEY_EXPLORE_MINUTES, function () {
            // Settings for queries
            $maxAnimePerCategory = 10;
            $maxAnimeForBanners = 5;

            // New movies
            $query_WINTER = Anime::where('nsfw', false)
                ->limit($maxAnimePerCategory)
                ->whereIn('tvdb_id', [79911, 272369, 316038, 121891, 123661, 80044])
                ->get();

            // Randomly chosen
            $query_RAND = Anime::where('nsfw', false)
                ->limit($maxAnimePerCategory)
                ->inRandomOrder()
                ->get();

            // Newly added Anime
            $query_NAA = Anime::where('nsfw', false)
                ->orderBy('created_at', 'DESC')
                ->limit($maxAnimePerCategory)
                ->get();

            // Add all the categories together
            $categoryArray = [
                Anime::formatAnimesAsCategory('Winter Themed', 'normal', $query_WINTER),
                Anime::formatAnimesAsCategory('Randomly Chosen', 'large', $query_RAND),
                Anime::formatAnimesAsCategory('Newly Added Anime', 'normal', $query_NAA)
            ];

            // Retrieve banner section
            $query_banners = Anime::where('nsfw', false)
                ->limit($maxAnimeForBanners)
                ->get();

            return [
                'categories'    => $categoryArray,
                'banners'       => Anime::formatAnimesAsThumbnail($query_banners)
            ];
        });

        // Return the response
        (new JSONResult())->setData($explorePage)->show();
    }

    /**
     * Returns detailed information about an Anime
     *
     * @param Request $request
     * @param Anime $anime
     */
    public function detailsAnime(Request $request, Anime $anime) {
        // Get the user rating for this Anime
        $userRating = 0.0;

        $foundRating = $anime->ratings()
            ->where('user_id', $request->user_id)
            ->first();

        if($foundRating)
            $userRating = $foundRating->rating;

        // Get the current library status
        $currentLibraryStatus = UserLibrary::getStringFromStatus(UserLibrary::STATUS_UNKNOWN);

        $foundLibraryStatus = UserLibrary::where([
            ['user_id' ,  '=', $request->user_id],
            ['anime_id' , '=', $anime->id]
        ])->first();

        if($foundLibraryStatus) $currentLibraryStatus = UserLibrary::getStringFromStatus($foundLibraryStatus->status);

        // Get the genres
        $genres = $anime->getGenres()->map(function($genre) {
            return $genre->formatForAnimeResponse();
        });

        // Build the response
        $animeArr = [
            'id'                    => $anime->id,
            'title'                 => $anime->title,
            'type'                  => $anime->getType(),
            'imdb_id'               => $anime->imdb_id,
            'network'               => $anime->network,
            'status'                => $anime->status,
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

        (new JSONResult())->setData(['anime' => $animeArr, 'user' => $userArr])->show();
    }

    /**
     * Returns actor information about an Anime
     *
     * @param Anime $anime
     * @throws \musa11971\TVDB\Exceptions\TVDBNotFoundException
     * @throws \musa11971\TVDB\Exceptions\TVDBUnauthorizedException
     */
    public function actorsAnime(Anime $anime) {
        // Get the actors
        $retActors = $anime->getActors()->map(function($actor) {
            return $actor->formatForResponse();
        });

        (new JSONResult())->setData([
            'total_actors'      => count($retActors),
            'actors'            => $retActors
        ])->show();
    }

    /**
     * Returns season information for an Anime
     *
     * @param Anime $anime
     */
    public function seasonsAnime(Anime $anime)
    {
        // Get the actors
        $foundSeasons = $anime->getSeasons()->map(function($season) {
            return $season->formatForResponse();
        });

        (new JSONResult())->setData(['seasons' => $foundSeasons])->show();
    }

    /**
     * Adds a rating for an Anime item
     *
     * @param Request $request
     * @param Anime $anime
     */
    public function rateAnime(Request $request, Anime $anime) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'rating' => 'bail|required|numeric|between:' . AnimeRating::MIN_RATING_VALUE . ',' . AnimeRating::MAX_RATING_VALUE
        ]);

        // Check validator
        if ($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        // Fetch the variables
        $givenRating = $request->input('rating');

        // Try to modify the rating if it already exists
        $foundRating = AnimeRating::where([
            ['anime_id', '=', $anime->id],
            ['user_id', '=', $request->user_id]
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
                    'anime_id' => $anime->id,
                    'user_id' => $request->user_id,
                    'rating' => $givenRating
                ]);
            }
        }

        (new JSONResult())->show();
    }

    /**
     * Retrieves Anime search results
     *
     * @param Request $request
     */
    public function search(Request $request) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'query' => 'bail|required|string|min:1'
        ]);

        // Check validator
        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

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

        // Show response
        (new JSONResult())->setData([
            'max_search_results'    => Anime::MAX_SEARCH_RESULTS,
            'results'               => $displayResults
        ])->show();
    }
}
