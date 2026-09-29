<?php

namespace App\View\Components\User;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class FavoritesSection extends Component
{
    /**
     * The user whose favorites are shown.
     *
     * @var User $user
     */
    public User $user;

    /**
     * The class of the favorited models.
     *
     * @var string $type
     */
    public string $type;

    /**
     * The title of the section.
     *
     * @var string $title
     */
    public string $title;

    /**
     * The URL of the full favorites list.
     *
     * @var string $seeAllUrl
     */
    public string $seeAllUrl;

    /**
     * The URL that re-renders the section.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * The most recently favorited models.
     *
     * @var Collection $favorites
     */
    public Collection $favorites;

    /**
     * Create a new component instance.
     *
     * @param User   $user
     * @param string $type
     */
    public function __construct(User $user, string $type)
    {
        $this->user = $user;
        $this->type = $type;
        $this->title = match ($type) {
            Anime::class => __('Favorite Anime'),
            Game::class => __('Favorite Games'),
            Manga::class => __('Favorite Manga'),
        };
        $this->seeAllUrl = match ($type) {
            Anime::class => route('profile.anime.favorites', $user),
            Game::class => route('profile.games.favorites', $user),
            Manga::class => route('profile.manga.favorites', $user),
        };
        $this->refreshUrl = match ($type) {
            Anime::class => route('profile.section', [$user, 'anime-favorites'], false),
            Game::class => route('profile.section', [$user, 'games-favorites'], false),
            Manga::class => route('profile.section', [$user, 'manga-favorites'], false),
        };
        $this->favorites = $this->loadFavorites();
    }

    /**
     * Whether the component should be rendered.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->favorites->isNotEmpty();
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.user.favorites-section');
    }

    /**
     * Load the most recently favorited models.
     *
     * @return Collection
     */
    protected function loadFavorites(): Collection
    {
        return $this->user->whereFavorited($this->type)
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }
}
