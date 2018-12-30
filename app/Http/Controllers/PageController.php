<?php

namespace App\Http\Controllers;

use App\Anime;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function anime($animeID) {
        $anime = Anime::find($animeID);

        if(!$anime) abort(404);

        return view('website.anime-page', [
            'page' => [
                'title' => $anime->title,
                'type' => 'video.tv_show',
                'image' => $anime->cached_poster
            ],
            'animeData' => [
                'id'            => $anime->id,
                'title'         => $anime->title,
                'episode_count' => $anime->episode_count,
                'poster'        => $anime->cached_poster
            ]
        ]);
    }
}
