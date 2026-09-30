<?php

namespace App\Http\Controllers\Web\Profile;

use App\Enums\UserLibraryKind;
use App\Enums\UserLibraryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetProfileLibraryRequest;
use App\Http\Requests\GetSearchIndexRequest;
use App\Models\User;
use App\Models\UserLibrary;
use App\Support\SearchCriteria;
use App\Support\SearchIndex;
use App\Traits\Controller\PaginatesTitles;
use App\Traits\Controller\ResolvesKind;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    use PaginatesTitles;
    use ResolvesKind;

    /**
     * Sends the user to the library overview for the active kind.
     *
     * @param GetProfileLibraryRequest $request
     * @param int $kind
     * @param User|null $user
     * @return RedirectResponse
     */
    public function index(GetProfileLibraryRequest $request, int $kind, ?User $user)
    {
        $data = $request->validated();
        $user = $user->id ? $user : auth()->user();
        $data['user'] = $user;

        if (empty($user)) {
            $intendedRoute = match ($kind) {
                UserLibraryKind::Anime => route('animelist'),
                UserLibraryKind::Manga => route('mangalist'),
                UserLibraryKind::Game  => route('gamelist'),
            };
            $request->session()->put('url.intended', $intendedRoute);
            return to_route('sign-in');
        }

        return match ($kind) {
            UserLibraryKind::Anime => to_route('profile.anime.library', $data),
            UserLibraryKind::Manga => to_route('profile.manga.library', $data),
            UserLibraryKind::Game  => to_route('profile.games.library', $data),
        };
    }

    /**
     * Show a user's library of a kind.
     *
     * @param GetSearchIndexRequest $request
     * @param User                  $user
     * @param int                   $kind
     *
     * @return Application|Factory|View
     */
    public function show(GetSearchIndexRequest $request, User $user, int $kind): Application|Factory|View
    {
        $modelClass = $this->modelClass($kind);
        $status = $this->status($request, $kind);
        $statusSlug = strtolower($this->statusDescription($kind, $status->value));

        $filters = $modelClass::webSearchFilters();
        unset($filters['library_status']);

        $criteria = SearchCriteria::fromRequest(
            $request,
            filters: $filters,
            orders: $modelClass::webSearchOrders(),
            searchTypes: $this->mediaTypes($kind),
        );

        $results = (new SearchIndex($modelClass, $criteria))
            ->hydrate(fn (Builder $query) => $this->hydrateTitles($query))
            ->library($user, [$status->value], excludeHidden: auth()->id() !== $user->id)
            ->paginate()
            ->withQueryString();

        $copy = match ($kind) {
            UserLibraryKind::Anime => [
                'titleSuffix' => __('Anime Library'),
                'randomUrl' => route('profile.anime.library.random', $user),
                'randomLabel' => 'random anime from ' . $statusSlug . ' library',
                'emptyImage' => 'empty_anime_library.webp',
                'emptyHeading' => __('No Shows'),
                'emptyDescription' => __('Add a show to your :x list and it will show up here.', ['x' => $statusSlug]),
            ],
            UserLibraryKind::Manga => [
                'titleSuffix' => __('Manga Library'),
                'randomUrl' => route('profile.manga.library.random', $user),
                'randomLabel' => 'random manga from ' . $statusSlug . ' library',
                'emptyImage' => 'empty_manga_library.webp',
                'emptyHeading' => __('No Manga'),
                'emptyDescription' => __('Add a manga to your :x list and it will show up here.', ['x' => $statusSlug]),
            ],
            UserLibraryKind::Game => [
                'titleSuffix' => __('Game Library'),
                'randomUrl' => route('profile.games.library.random', $user),
                'randomLabel' => 'random game from ' . $statusSlug . ' library',
                'emptyImage' => 'empty_game_library.webp',
                'emptyHeading' => __('No Games'),
                'emptyDescription' => __('Add a game to your :x list and it will show up here.', ['x' => $statusSlug]),
            ],
        };

        return view('profile.library.index', $copy + [
            'kind' => $kind,
            'user' => $user,
            'criteria' => $criteria,
            'results' => $results,
            'status' => $statusSlug,
            'defaultStatus' => strtolower($this->statusDescription($kind, UserLibraryStatus::InProgress)),
            'statuses' => $this->statuses($kind),
            'canonicalUrl' => match ($kind) {
                UserLibraryKind::Anime => route('profile.anime.library', $user),
                UserLibraryKind::Manga => route('profile.manga.library', $user),
                UserLibraryKind::Game => route('profile.games.library', $user),
            },
        ]);
    }

    /**
     * Send the visitor to a random title in a user's library list.
     *
     * @param Request $request
     * @param User    $user
     * @param int     $kind
     *
     * @return RedirectResponse
     */
    public function random(Request $request, User $user, int $kind): RedirectResponse
    {
        $status = $this->status($request, $kind);

        $item = $user->whereTracked($this->modelClass($kind))
            ->when(auth()->id() !== $user->id, function ($query) {
                $query->where(UserLibrary::TABLE_NAME . '.is_hidden', '=', false);
            })
            ->wherePivot('status', $status->value)
            ->withoutIgnoreList()
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
     * The library status the request names.
     *
     * @param Request $request
     * @param int     $kind
     *
     * @return UserLibraryStatus
     */
    protected function status(Request $request, int $kind): UserLibraryStatus
    {
        return UserLibraryStatus::fromSlug($request->string('status')->value())
            ?? UserLibraryStatus::InProgress();
    }

    /**
     * The library statuses of a kind keyed by their value.
     *
     * @param int $kind
     *
     * @return array
     */
    protected function statuses(int $kind): array
    {
        return match ($kind) {
            UserLibraryKind::Anime => UserLibraryStatus::asAnimeSelectArray(),
            UserLibraryKind::Manga => UserLibraryStatus::asMangaSelectArray(),
            UserLibraryKind::Game => UserLibraryStatus::asGameSelectArray(),
        };
    }

    /**
     * The description of a library status for a kind.
     *
     * @param int $kind
     * @param int $status
     *
     * @return string
     */
    protected function statusDescription(int $kind, int $status): string
    {
        return match ($kind) {
            UserLibraryKind::Anime => UserLibraryStatus::getAnimeDescription($status),
            UserLibraryKind::Manga => UserLibraryStatus::getMangaDescription($status),
            UserLibraryKind::Game => UserLibraryStatus::getGameDescription($status),
        };
    }
}
