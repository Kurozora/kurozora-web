<?php

namespace App\Http\Middleware;

use App\AnimeEpisode;
use App\AnimeSeason;
use App\Helpers\JSONResult;
use Closure;

class CheckAnimeInLibrary
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
	    // Check whether the Anime exists in the user's library
	    if (!$request->episode->season->anime->isInLibrary())
		    return JSONResult::error('Can\'t find ' . $request->episode->season->anime->title . ' in user\'s library.');

        return $next($request);
    }
}
