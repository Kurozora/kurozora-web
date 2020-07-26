<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\AddAnimeFavoriteRequest;
use App\Http\Requests\GetAnimeFavoritesRequest;
use App\Http\Resources\AnimeResourceBasic;
use App\User;

class FavoriteAnimeController extends Controller
{
    /**
     * Adds an anime to the user's favorites.
     *
     * @param AddAnimeFavoriteRequest $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    function addFavorite(AddAnimeFavoriteRequest $request, User $user) {
        $data = $request->validated();

        $user->favoriteAnime()->detach($data['anime_id']);

        if($data['is_favorite'])
            $user->favoriteAnime()->attach($data['anime_id']);

        return JSONResult::success([
            'data' => [
                'is_favorite' => (bool) $data['is_favorite']
            ]
        ]);
    }

    /**
     * Returns a list of the user's favorite anime.
     *
     * @param GetAnimeFavoritesRequest $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    function getFavorites(GetAnimeFavoritesRequest $request, User $user) {
        return JSONResult::success([
            'data' => AnimeResourceBasic::collection($user->favoriteAnime()->get())
        ]);
    }
}
