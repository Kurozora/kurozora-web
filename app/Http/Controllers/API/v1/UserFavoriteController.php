<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\FavoriteKind;
use App\Enums\UserLibraryKind;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserFavoriteRequest;
use App\Http\Requests\GetUserFavoritesRequest;
use App\Http\Resources\AnimeResourceBasic;
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\GameResourceBasic;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\LiteratureResourceBasic;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\PersonResourceIdentity;
use App\Http\Resources\SongResourceIdentity;
use App\Http\Resources\StudioResourceIdentity;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use App\Models\User;
use App\Models\UserFavorite;
use App\Models\UserLibrary;
use App\Rules\ValidateModelIsTracked;
use App\Traits\Controller\WithStateVersionETag;
use App\Traits\Model\Remindable;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserFavoriteController extends Controller
{
    use WithStateVersionETag;

    /**
     * Returns a list of the user's favorite models.
     *
     * @param GetUserFavoritesRequest $request
     * @param User                    $user
     *
     * @return JsonResponse
     */
    function index(GetUserFavoritesRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        // Overlay branch: `ids` present switches to the per-screen sparse map.
        if (!empty($data['ids'])) {
            return $this->overlay($request, $user);
        }

        $kind = (int) ($data['kind'] ?? UserLibraryKind::Anime);
        $limit = (int) ($data['limit'] ?? 25);

        $fingerprint = [
            'kind' => $kind,
            'limit' => $limit,
            'cursor' => $request->query('cursor'),
            'targetUserId' => $user->id,
            'isOwner' => auth()->id() === $user->id,
        ];
        $notModified = $this->returnIfNotModified($request, $user, $fingerprint);
        if ($notModified !== null) {
            return $notModified;
        }
        $etag = $this->stateVersionETag($user, $fingerprint);

        // Get morph class
        $morphClass = match ($kind) {
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
            default => Anime::class,
        };

        // Paginate the favorited model
        $userFavorites = $user->whereFavorited($morphClass)
            ->when(auth()->id() !== $user->id, function (Builder $query) use ($user) {
                $query->join(UserLibrary::TABLE_NAME, UserFavorite::TABLE_NAME . '.favorable_id', '=', UserLibrary::TABLE_NAME . '.trackable_id')
                    ->whereColumn(UserLibrary::TABLE_NAME . '.trackable_type', '=', UserFavorite::TABLE_NAME . '.favorable_type')
                    ->where(UserLibrary::TABLE_NAME . '.user_id', '=', $user->id)
                    ->where(UserLibrary::TABLE_NAME . '.is_hidden', '=', false);
            })
            ->with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin', 'mediaRatings' => function ($query) use ($user) {
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
            ->cursorPaginate($limit);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $userFavorites->nextPageUrl() ?? '');

        // Get data collection
        $data = match ($kind) {
            UserLibraryKind::Manga => ['literatures' => LiteratureResourceBasic::collection($userFavorites)],
            UserLibraryKind::Game => ['games' => GameResourceBasic::collection($userFavorites)],
            default => ['shows' => AnimeResourceBasic::collection($userFavorites)],
        };

        return JSONResult::success([
            'data' => $data,
            'next' => empty($nextPageURL) ? null : $nextPageURL
        ])->withHeaders($this->stateVersionHeaders($etag, $user));
    }

    /**
     * Adds a model to the user's favorites.
     *
     * @param CreateUserFavoriteRequest $request
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    function create(CreateUserFavoriteRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Get the authenticated user
        $user = auth()->user();

        // Get the models
        $favoriteKind = FavoriteKind::fromValue((int) $data['kind']);
        $modelClass = $this->modelClassForFavoriteKind($favoriteKind);
        $modelIDs = $data['model_ids'] ?? [$data['model_id']];
        $modelQuery = $modelClass::withoutGlobalScopes()
            ->whereIn('id', $modelIDs);

        // `translations` only exists on library-trackable models
        if ($favoriteKind->isLibraryTrackable()) {
            $modelQuery->with(['translations']);
        }

        $models = $modelQuery->get();

        // Library-trackable favorites require an existing library entry; other kinds skip the gate.
        if ($favoriteKind->isLibraryTrackable()) {
            validator([
                'models' => $models
            ], [
                'models' => [new ValidateModelIsTracked],
            ])
                ->validate();
        }

        $isFavorited = DB::transaction(function () use ($user, $models) {
            $result = $user->toggleFavorite($models);
            $user->bumpStateVersion();
            return $result;
        });

        // Successful response
        return JSONResult::success([
            'data' => [
                'isFavorited' => $isFavorited
            ]
        ]);
    }

    /**
     * Returns the Eloquent model class corresponding to the given favorite kind.
     *
     * @param FavoriteKind $kind
     * @return string
     */
    private function modelClassForFavoriteKind(FavoriteKind $kind): string
    {
        return match ($kind->value) {
            FavoriteKind::Manga => Manga::class,
            FavoriteKind::Game => Game::class,
            FavoriteKind::Character => Character::class,
            FavoriteKind::Person => Person::class,
            FavoriteKind::Studio => Studio::class,
            FavoriteKind::Song => Song::class,
            default => Anime::class,
        };
    }

    /**
     * Returns the user's favorite-state Resources for the requested favorable IDs.
     *
     * @throws AuthorizationException
     * @throws InvalidEnumKeyException
     * @throws InvalidEnumMemberException
     */
    private function overlay(GetUserFavoritesRequest $request, User $user): JsonResponse
    {
        if (auth()->id() !== $user->id) {
            throw new AuthorizationException(__('Favorites state is currently visible only to its owner.'));
        }

        $data = $request->validated();
        $favoriteKind = FavoriteKind::fromValue((int) ($data['kind'] ?? FavoriteKind::Anime));
        $ids = array_values(array_unique($data['ids']));
        sort($ids);

        $fingerprint = [
            'kind' => $favoriteKind->value,
            'ids' => $ids,
        ];
        $notModified = $this->returnIfNotModified($request, $user, $fingerprint);
        if ($notModified !== null) {
            return $notModified;
        }
        $etag = $this->stateVersionETag($user, $fingerprint);

        $morphClass = $this->modelClassForFavoriteKind($favoriteKind);
        $relationshipKey = $this->relationshipKeyForFavoriteKind($favoriteKind);
        $identityClass = $this->identityClassForFavoriteKind($favoriteKind);

        $entries = [];

        UserFavorite::where('user_id', '=', $user->id)
            ->where('favorable_type', '=', $morphClass)
            ->whereIn('favorable_id', $ids)
            ->select(['favorable_id', 'created_at', 'updated_at'])
            ->cursor()
            ->each(function ($row) use (&$entries, $relationshipKey, $identityClass) {
                $entries[] = [
                    'attributes' => [
                        'createdAt' => $row->created_at ? Carbon::parse($row->created_at)->timestamp : null,
                        'updatedAt' => $row->updated_at ? Carbon::parse($row->updated_at)->timestamp : null,
                    ],
                    'relationships' => [
                        $relationshipKey => [
                            'data' => $identityClass::collection([$row->favorable_id]),
                        ],
                    ],
                ];
            });

        return JSONResult::success([
            'data' => $entries,
        ])->withHeaders($this->stateVersionHeaders($etag, $user));
    }

    /**
     * Returns the per-type relationship key used in the overlay response for the given kind.
     *
     * @param FavoriteKind $kind
     * @return string
     */
    private function relationshipKeyForFavoriteKind(FavoriteKind $kind): string
    {
        return match ($kind->value) {
            FavoriteKind::Manga => 'literatures',
            FavoriteKind::Game => 'games',
            FavoriteKind::Character => 'characters',
            FavoriteKind::Person => 'people',
            FavoriteKind::Studio => 'studios',
            FavoriteKind::Song => 'songs',
            default => 'shows',
        };
    }

    /**
     * Returns the identity resource class used to render the favorable for the given kind.
     *
     * @param FavoriteKind $kind
     * @return string
     */
    private function identityClassForFavoriteKind(FavoriteKind $kind): string
    {
        return match ($kind->value) {
            FavoriteKind::Manga => LiteratureResourceIdentity::class,
            FavoriteKind::Game => GameResourceIdentity::class,
            FavoriteKind::Character => CharacterResourceIdentity::class,
            FavoriteKind::Person => PersonResourceIdentity::class,
            FavoriteKind::Studio => StudioResourceIdentity::class,
            FavoriteKind::Song => SongResourceIdentity::class,
            default => AnimeResourceIdentity::class,
        };
    }
}
