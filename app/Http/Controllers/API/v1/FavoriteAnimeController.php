<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddAnimeFavoriteRequest;
use App\Http\Requests\GetAnimeFavoritesRequest;
use App\Http\Resources\AnimeResourceBasic;
use App\Models\User;
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
        $data = $request->validated();

        // Paginate the favorited anime
        $favoriteAnime = $user->favorite_anime()->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $favoriteAnime->nextPageUrl());

        return JSONResult::success([
            'data' => AnimeResourceBasic::collection($favoriteAnime),
            'next' => empty($nextPageURL) ? null : $nextPageURL
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
        $user = auth()->user();

        $isAlreadyFavorited = $user->favorite_anime()->where('anime_id', $animeID)->exists();

        if ($isAlreadyFavorited) { // Unfavorite the show
            $user->favorite_anime()->detach($animeID);
        } else { // Favorite the show
            $user->favorite_anime()->attach($animeID);
        }

        return JSONResult::success([
            'data' => [
                'isFavorited' => !$isAlreadyFavorited
            ]
        ]);
    }
}