<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\AddAnimeFavoriteRequest;
use App\Http\Requests\GetAnimeFavoritesRequest;
use App\Http\Resources\AnimeResourceBasic;
use App\User;
use Auth;
use Illuminate\Http\JsonResponse;

class FavoriteAnimeController extends Controller
{
    /**
     * Returns a list of the user's favorite anime.
     *
     * @param GetAnimeFavoritesRequest $request
     * @param User $user
     * @return JsonResponse
     */
    function getFavorites(GetAnimeFavoritesRequest $request, User $user): JsonResponse
    {
        return JSONResult::success([
            'data' => AnimeResourceBasic::collection($user->favoriteAnime()->get())
        ]);
    }

    /**
     * Adds an anime to the user's favorites.
     *
     * @param AddAnimeFavoriteRequest $request
     * @return JsonResponse
     */
    function addFavorite(AddAnimeFavoriteRequest $request): JsonResponse
    {
        $animeID = $request->input('anime_id');

        /** @var User $user */
        $user = Auth::user();

        $isAlreadyFavorited = $user->favoriteAnime()->where('anime_id', $animeID)->exists();

        if($isAlreadyFavorited) // Unfavorite the show
            $user->favoriteAnime()->detach($animeID);
        else // Favorite the show
            $user->favoriteAnime()->attach($animeID);

        return JSONResult::success([
            'data' => [
                'isFavorited' => !$isAlreadyFavorited
            ]
        ]);
    }
}
