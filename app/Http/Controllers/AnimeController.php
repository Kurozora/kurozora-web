<?php

namespace App\Http\Controllers;

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


        // Add all the categories together
        $categoryArray = [
            Anime::formatAnimesAsCategory('Top Anime of All time', 'normal', $query_TAOAT),
            Anime::formatAnimesAsCategory('Top New Movies', 'normal', $query_TNM)
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
            'synopsis'              => $anime->getSynopsis(),
            'runtime'               => $anime->getRuntime(),
            'poster'                => $anime->getPoster(false),
            'poster_thumbnail'      => $anime->getPoster(true),
            'background'            => $anime->getBackground(false),
            'background_thumbnail'  => $anime->getBackground(true),
            'nsfw'                  => (bool) $anime->nsfw
        ];

        (new JSONResult())->setData(['anime' => $returnArr])->show();
    }
}
