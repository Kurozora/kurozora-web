<?php

namespace App\Http\Controllers\Web\Profile;

use App\Enums\UserLibraryKind;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetSearchIndexRequest;
use App\Models\User;
use App\Models\UserLibrary;
use App\Support\SearchCriteria;
use App\Support\SearchIndex;
use App\Traits\Controller\PaginatesTitles;
use App\Traits\Controller\ResolvesKind;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Laravel\Scout\Builder as ScoutBuilder;

class FavoriteController extends Controller
{
    use PaginatesTitles;
    use ResolvesKind;

    /**
     * Show a user's favorite titles of a kind.
     *
     * @param GetSearchIndexRequest $request
     * @param User                  $user
     * @param int                   $kind
     *
     * @return Application|Factory|View
     */
    public function index(GetSearchIndexRequest $request, User $user, int $kind): Application|Factory|View
    {
        $modelClass = $this->modelClass($kind);

        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: $modelClass::webSearchFilters(),
            orders: $modelClass::webSearchOrders(),
            searchTypes: $this->mediaTypes($kind),
        );

        $constrain = $this->constraint($modelClass, $user, $criteria->libraryStatuses());
        $scoped = fn () => $user->whereFavorited($modelClass)
            ->withoutIgnoreList()
            ->tap($constrain);

        $results = (new SearchIndex($modelClass, $criteria))
            ->from($scoped())
            ->hydrate(fn (Builder $query) => $this->hydrateTitles($query))
            ->search(function (ScoutBuilder $query) use ($scoped) {
                $query->whereIn('id', $scoped()->limit(2000)->pluck('favorable_id')->toArray());
            })
            ->paginate()
            ->withQueryString();

        $copy = match ($kind) {
            UserLibraryKind::Anime => [
                'title' => __(':x’s Favorite Anime', ['x' => $user->username]),
                'randomUrl' => route('profile.anime.favorites.random', $user),
                'randomLabel' => 'random anime',
                'emptyImage' => 'empty_anime_library.webp',
                'emptyHeading' => __('No Favorite Anime'),
                'emptyDescription' => __('Favorite an anime and it will show up here.'),
                'canonicalUrl' => route('profile.anime.favorites', $user),
            ],
            UserLibraryKind::Manga => [
                'title' => __(':x’s Favorite Manga', ['x' => $user->username]),
                'randomUrl' => route('profile.manga.favorites.random', $user),
                'randomLabel' => 'random manga',
                'emptyImage' => 'empty_manga_library.webp',
                'emptyHeading' => __('No Favorite Mangas'),
                'emptyDescription' => __('Favorite a manga and it will show up here.'),
                'canonicalUrl' => route('profile.manga.favorites', $user),
            ],
            UserLibraryKind::Game => [
                'title' => __(':x’s Favorite Game', ['x' => $user->username]),
                'randomUrl' => route('profile.games.favorites.random', $user),
                'randomLabel' => 'random game',
                'emptyImage' => 'empty_game_library.webp',
                'emptyHeading' => __('No Favorite Games'),
                'emptyDescription' => __('Favorite a game and it will show up here.'),
                'canonicalUrl' => route('profile.games.favorites', $user),
            ],
        };

        return view('profile.library.favorites', $copy + [
            'kind' => $kind,
            'user' => $user,
            'criteria' => $criteria,
            'results' => $results,
        ]);
    }

    /**
     * Send the visitor to a random favorite title of a user.
     *
     * @param User $user
     * @param int  $kind
     *
     * @return RedirectResponse
     */
    public function random(User $user, int $kind): RedirectResponse
    {
        $modelClass = $this->modelClass($kind);

        $item = $user->whereFavorited($modelClass)
            ->tap($this->constraint($modelClass, $user))
            ->inRandomOrder()
            ->first();

        if ($item === null) {
            return back();
        }

        return match ($kind) {
            UserLibraryKind::Anime => to_route('anime.details', $item),
            UserLibraryKind::Manga => to_route('manga.details', $item),
            UserLibraryKind::Game => to_route('games.details', $item),
        };
    }

    /**
     * The constraint scoping the favorites to library statuses and visible entries.
     *
     * @param string $modelClass
     * @param User   $user
     * @param array  $libraryStatuses
     *
     * @return Closure
     */
    protected function constraint(string $modelClass, User $user, array $libraryStatuses = []): Closure
    {
        return function (Builder $query) use ($modelClass, $user, $libraryStatuses) {
            $query->when(!empty($libraryStatuses), function ($query) use ($modelClass, $user, $libraryStatuses) {
                $query->join(UserLibrary::TABLE_NAME, function ($join) use ($modelClass, $user, $libraryStatuses) {
                    $join->on(UserLibrary::TABLE_NAME . '.trackable_id', '=', $modelClass::TABLE_NAME . '.id')
                        ->where(UserLibrary::TABLE_NAME . '.user_id', '=', $user->id)
                        ->where(UserLibrary::TABLE_NAME . '.trackable_type', '=', $modelClass)
                        ->whereIn(UserLibrary::TABLE_NAME . '.status', $libraryStatuses)
                        ->whereNull(UserLibrary::TABLE_NAME . '.deleted_at');
                });
            })
                ->when(auth()->id() !== $user->id, function ($query) use ($modelClass, $user) {
                    $query->whereExists(function ($subQuery) use ($modelClass, $user) {
                        $subQuery->from(UserLibrary::TABLE_NAME)
                            ->whereColumn(UserLibrary::TABLE_NAME . '.trackable_id', $modelClass::TABLE_NAME . '.id')
                            ->where(UserLibrary::TABLE_NAME . '.trackable_type', '=', $modelClass)
                            ->where(UserLibrary::TABLE_NAME . '.user_id', '=', $user->id)
                            ->where(UserLibrary::TABLE_NAME . '.is_hidden', '=', false)
                            ->whereNull(UserLibrary::TABLE_NAME . '.deleted_at');
                    });
                });
        };
    }
}
