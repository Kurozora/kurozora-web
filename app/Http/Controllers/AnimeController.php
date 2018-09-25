<?php

namespace App\Http\Controllers;

use App\Actor;
use App\Anime;
use App\AnimeRating;
use App\Helpers\JSONResult;
use App\User;
use Illuminate\Support\Facades\Artisan;
use Validator;
use Illuminate\Http\Request;

class AnimeController extends Controller
{
    /**
     * Returns the necessary data for the Anime explore page
     */
    public function exploreAnime() {
        // Settings for queries
        $maxAnimePerCategory    = 10;
        $maxAnimeForBanners     = 5;

        // Top anime of all time
        $query_TAOAT = Anime::where('nsfw', false)
            ->where('average_rating', '!=', '0')
            ->orderBy('average_rating', 'DESC')
            ->limit($maxAnimePerCategory)
            ->get();

        // New movies
        $query_NM = Anime::where('nsfw', false)
            ->where('type', '=', Anime::ANIME_TYPE_MOVIE)
            ->orderBy('created_at', 'DESC')
            ->limit($maxAnimePerCategory)
            ->get();

        // Top episodes this month
        $query_TETM = Anime::where('nsfw', false)
            ->orderBy('title', 'ASC')
            ->limit($maxAnimePerCategory)
            ->get();

        // Newly added Anime
        $query_NAA = Anime::where('nsfw', false)
            ->orderBy('created_at', 'DESC')
            ->limit($maxAnimePerCategory)
            ->get();

        // Add all the categories together
        $categoryArray = [
            Anime::formatAnimesAsCategory('Top Anime of All time', 'normal', $query_TAOAT),
            Anime::formatAnimesAsCategory('New Movies', 'normal', $query_NM),
            Anime::formatAnimesAsCategory('Top Episodes This Month', 'large', $query_TETM),
            Anime::formatAnimesAsCategory('Newly Added Anime', 'normal', $query_NAA)
        ];

        // Retrieve banner section
        $query_banners = Anime::where('nsfw', false)
                        ->limit($maxAnimeForBanners)
                        ->get();

        (new JSONResult())->setData([
            'categories'    => $categoryArray,
            'banners'       => Anime::formatAnimesAsThumbnail($query_banners)
        ])->show();
    }

    /**
     * Returns detailed information about an Anime
     */
    public function detailsAnime(Request $request, $animeID) {
    	// Validate the inputs
        $validator = Validator::make($request->all(), [
            'session_secret'    => 'bail|required|exists:sessions,secret',
            'user_id'           => 'bail|required|numeric|exists:users,id'
        ]);

        // Fetch the variables
        $givenSecret    = $request->input('session_secret');
        $givenUserID    = $request->input('user_id');

        // Check authentication
        if($validator->fails() || !User::authenticateSession($givenUserID, $givenSecret))
            (new JSONResult())->setError('The server rejected your credentials. Please restart the app.')->show();

        $anime = Anime::find($animeID);

        // The Anime item does not exist
        if(!$anime)
            (new JSONResult())->setError('Unable to retrieve the details for the specified anime.')->show();

        // Get the user rating for this Anime
        $userRating = 0.0;

        $foundRating = AnimeRating::where([
        	['user_id' , '=', $givenUserID],
        	['anime_id' , '=', $anime->id]
        ])->first();

        if($foundRating) $userRating = $foundRating->rating;

        // Build the response
        $animeArr = [
            'id'                    => $anime->id,
            'title'                 => $anime->title,
            'type'                  => $anime->getType(),
            'episodes'              => $anime->episode_count,
            'seasons'               => $anime->season_count,
            'average_rating'        => $anime->average_rating,
            'rating_count'          => $anime->rating_count,
            'synopsis'              => $anime->getSynopsis(),
            'runtime'               => $anime->getRuntime(),
            'watch_rating'          => $anime->getWatchRating(),
            'poster'                => $anime->getPoster(false),
            'poster_thumbnail'      => $anime->getPoster(true),
            'background'            => $anime->getBackground(false),
            'background_thumbnail'  => $anime->getBackground(true),
            'nsfw'                  => (bool) $anime->nsfw
        ];

        $userArr = [
        	'current_rating'		=> $userRating
       	];

        (new JSONResult())->setData(['anime' => $animeArr, 'user' => $userArr])->show();
    }

    /**
     * Returns actor information about an Anime
     *
     * @param $animeID
     */
    public function actorsAnime($animeID) {
        $anime = Anime::find($animeID);

        // The Anime item does not exist
        if(!$anime)
            (new JSONResult())->setError('Unable to retrieve actor data for the specified anime.')->show();

        // Get the actors
        $retActors = [];
        $actors = $anime->getActors();

        foreach($actors as $actor)
            $retActors[] = Actor::formatForResponse($actor);

        (new JSONResult())->setData(['actors' => $retActors])->show();
    }

    /**
     * Returns episode information for an Anime
     *
     * @param Request $request
     * @param int $animeID
     */
    public function episodesAnime(Request $request, $animeID) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'season' => 'bail|required|numeric'
        ]);

        if($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        // Get the Anime
        $anime = Anime::find($animeID);

        // The Anime item does not exist
        if(!$anime)
            (new JSONResult())->setError('Unable to retrieve episode data for the specified anime.')->show();

        // Get the episodes
        $season = $request->input('season');

        $episodes = $anime->getEpisodes($season);
        $endEpisodes = [];

        foreach($episodes as $episode)
            $endEpisodes[] = $episode->formatEpisodeData();

        (new JSONResult())->setData([
            'episode_count' => count($endEpisodes),
            'episodes'      => $endEpisodes
        ])->show();
    }

    /**
     * Adds a rating for an Anime item
     *
     * @param Request $request
     * @param $animeID
     */
    public function rateAnime(Request $request, $animeID) {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'session_secret' => 'bail|required|exists:sessions,secret',
            'user_id' => 'bail|required|numeric|exists:users,id',
            'rating' => 'bail|required|numeric|between:' . AnimeRating::MIN_RATING_VALUE . ',' . AnimeRating::MAX_RATING_VALUE
        ]);

        // Get the anime
        $anime = Anime::find($animeID);

        // The Anime item does not exist
        if(!$anime)
            (new JSONResult())->setError('The anime you are trying to rate was not found.')->show();

        // Fetch the variables
        $givenSecret = $request->input('session_secret');
        $givenUserID = $request->input('user_id');
        $givenRating = $request->input('rating');

        // Check session
        if(!User::authenticateSession($givenUserID, $givenSecret))
            (new JSONResult())->setError('The server rejected your session. Please restart the app.')->show();

        // Check validator
        if ($validator->fails())
            (new JSONResult())->setError($validator->errors()->first())->show();

        // Try to modify the rating if it already exists
        $foundRating = AnimeRating::where([
            ['anime_id', '=', $anime->id],
            ['user_id', '=', $givenUserID]
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
                    'user_id' => $givenUserID,
                    'rating' => $givenRating
                ]);
            }
        }

        (new JSONResult())->show();
    }
}
