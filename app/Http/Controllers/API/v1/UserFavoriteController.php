<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\UserLibraryKind;
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
use App\Models\UserFavorite;
use App\Models\UserLibrary;
use App\Traits\Model\Remindable;
use Illuminate\Database\Eloquent\Builder;
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
        $library = (int) ($data['library'] ?? UserLibraryKind::Anime);

        // Get morph class
        $morphClass = match ($library) {
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
            default => Anime::class,
        };

        // Paginate the favorited model
        $userFavorites = $user->whereFavorited($morphClass)
            ->when(auth()->user() !== $user, function (Builder $query) use ($user) {
                $query->join(UserLibrary::TABLE_NAME, UserFavorite::TABLE_NAME . '.favorable_id', '=', UserLibrary::TABLE_NAME . '.trackable_id')
                    ->whereColumn(UserLibrary::TABLE_NAME . '.trackable_type', '=', UserFavorite::TABLE_NAME . '.favorable_type')
                    ->where(UserLibrary::TABLE_NAME . '.user_id', '=', $user->id)
                    ->where(UserLibrary::TABLE_NAME . '.is_hidden', '=', false);
            })
            ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translations', 'tv_rating', 'mediaRatings' => function ($query) use ($user) {
                $query->where([
                    ['user_id', '=', $user->id]
                ]);
            }, 'library' => function ($query) use ($user) {
                $query->where('user_id', '=', $user->id);
            }])
            ->withExists([
                'favoriters as isFavorited' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                },
            ])
            ->when(in_array(Remindable::class, class_uses_recursive($morphClass)), function ($query) use ($user) {
                // Add your logic here if the trait is used
                $query->withExists([
                    'reminderers as isReminded' => function ($query) use ($user) {
                        $query->where('user_id', '=', $user->id);
                    },
                ]);
            })
            ->paginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $userFavorites->nextPageUrl());

        // Get data collection
        $data = match ($library) {
            UserLibraryKind::Manga => ['literatures' => LiteratureResourceBasic::collection($userFavorites)],
            UserLibraryKind::Game => ['games' => GameResourceBasic::collection($userFavorites)],
            default => ['shows' => AnimeResourceBasic::collection($userFavorites)],
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
            $libraryKind = UserLibraryKind::fromValue((int) $data['library']);
            $model = match ($libraryKind->value) {
                UserLibraryKind::Manga  => Manga::findOrFail($modelID),
                UserLibraryKind::Game   => Game::findOrFail($modelID),
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
