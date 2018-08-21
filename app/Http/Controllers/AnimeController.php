<?php

namespace App\Http\Controllers;

use App\Actor;
use App\Anime;
use App\Helpers\JSONResult;
use TVDB;

class AnimeController extends Controller
{
    /**
     * Returns the necessary data for the Anime explore page
     *
     * URL: /api/v1/anime/explore
     */
    public function exploreAnime() {
        // Settings for queries
        $maxAnimePerCategory    = 10;
        $maxAnimeForBanners     = 5;

        // Top anime of all time
        $query_TAOAT = Anime::where('nsfw', false)
            ->orderBy('title', 'asc')
            ->limit($maxAnimePerCategory)
            ->get();

        // Top new movies
        $query_TNM = Anime::where('nsfw', false)
            ->orderBy('title', 'asc')
            ->limit($maxAnimePerCategory)
            ->get();

        // Top episodes this month
        $query_TETM = Anime::where('nsfw', false)
            ->orderBy('title', 'asc')
            ->limit($maxAnimePerCategory)
            ->get();

        // Newly added Anime
        $query_NAA = Anime::where('nsfw', false)
            ->orderBy('title', 'asc')
            ->limit($maxAnimePerCategory)
            ->get();

        // Add all the categories together
        $categoryArray = [
            Anime::formatAnimesAsCategory('Top Anime of All time', 'normal', $query_TAOAT),
            Anime::formatAnimesAsCategory('Top New Movies', 'normal', $query_TNM),
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
     *
     * @param int $animeID
     */
    public function detailsAnime($animeID) {
        $anime = Anime::find($animeID);

        // The Anime item does not exist
        if(!$anime)
            (new JSONResult())->setError('Unable to retrieve the details for the specified anime.')->show();

        // Build the response
        $returnArr = [
            'id'                    => $anime->id,
            'title'                 => $anime->title,
            'type'                  => $anime->getType(),
            'synopsis'              => $anime->getSynopsis(),
            'runtime'               => $anime->getRuntime(),
            'watch_rating'          => $anime->getWatchRating(),
            'poster'                => $anime->getPoster(false),
            'poster_thumbnail'      => $anime->getPoster(true),
            'background'            => $anime->getBackground(false),
            'background_thumbnail'  => $anime->getBackground(true),
            'nsfw'                  => (bool) $anime->nsfw
        ];

        (new JSONResult())->setData(['anime' => $returnArr])->show();
    }

    /**
     * Returns actor information about an Anime
     *
     * @param int $animeID
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
}
