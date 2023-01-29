<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\UserLibraryType;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserFavoriteRequest;
use App\Http\Requests\GetUserFavoritesRequest;
use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\GameResourceBasic;
use App\Http\Resources\LiteratureResourceBasic;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserFavoriteController extends Controller
{
    /**
     * Returns a list of the user's favorite models.
     *
     * @param GetUserFavoritesRequest $request
     * @param User $user
     * @return JsonResponse
     */
    function index(GetUserFavoritesRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        // Get morph class
        $morphClass = match ((int) ($data['library'] ?? UserLibraryType::Anime)) {
            UserLibraryType::Manga => Manga::class,
            UserLibraryType::Game => Game::class,
            default => Anime::class,
        };

        // Paginate the favorited model
        $userFavorites = $user->whereFavorited($morphClass)
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $userFavorites->nextPageUrl());

        // Get data collection
        $data = match ((int) ($data['library'] ?? UserLibraryType::Anime)) {
            UserLibraryType::Manga => LiteratureResourceBasic::collection($userFavorites),
            UserLibraryType::Game => GameResourceBasic::collection($userFavorites),
            default => AnimeResourceBasic::collection($userFavorites),
        };

        return JSONResult::success([
            'data' => $data,
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ]);
    }

    /**
     * Adds a model to the user's favorites.
     *
     * @param CreateUserFavoriteRequest $request
     * @return JsonResponse
     */
    function create(CreateUserFavoriteRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get the model
        if (!empty($data['anime_id'])) {
            $modelID = $data['anime_id'];
            $model = Anime::findOrFail($modelID);
        } else {
            $modelID = $data['model_id'];
            $libraryType = UserLibraryType::fromValue((int) $data['library']);
            $model = match ($libraryType->value) {
                UserLibraryType::Manga  => Manga::findOrFail($modelID),
                UserLibraryType::Game   => Game::findOrFail($modelID),
                default                 => Anime::findOrFail($modelID),
            };
        }

        // Successful response
        return JSONResult::success([
            'data' => [
                'isFavorited' => !is_bool($user->toggleFavorite($model))
            ]
        ]);
    }
}
