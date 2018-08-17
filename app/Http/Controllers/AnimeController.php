<?php

namespace App\Http\Controllers;

use App\Anime;
use App\Helpers\JSONResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnimeController extends Controller
{
    /**
        /api/v1/anime/explore

        expects:
        - n/a
    **/
    public function exploreAnime(Request $request) {
        // Settings for queries
        $columnsToSelect = ['id', 'title', 'cached_poster_thumbnail as poster_url', 'cached_background_thumbnail as background_url'];
        $maxAnimePerCategory    = 10;
        $maxAnimeForBanners     = 5;

        // Add all the categories together
        $categoryArray = [
            [
                'title' => 'Top Anime of All Time',
                'type'  => 'normal',
                'shows' => Anime::select($columnsToSelect)
                                    ->where('nsfw', false)
                                    ->orderBy('title', 'asc')
                                    ->limit($maxAnimePerCategory)
                                    ->get()
            ],
            [
                'title' => 'Top New Movies',
                'type'  => 'normal',
                'shows' => Anime::select($columnsToSelect)
                                    ->where('nsfw', false)
                                    ->orderBy('title', 'asc')
                                    ->limit($maxAnimePerCategory)
                                    ->get()
            ],
        ];

        // Retrieve banner section
        $bannerArray = Anime::select($columnsToSelect)
                        ->where('nsfw', false)
                        ->limit($maxAnimeForBanners)
                        ->get();

        (new JSONResult())->setData([
            'categories'    => $categoryArray,
            'banners'       => $bannerArray
        ])->show();
    }
}
