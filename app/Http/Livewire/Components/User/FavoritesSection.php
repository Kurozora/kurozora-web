<?php

namespace App\Http\Livewire\Components\User;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class FavoritesSection extends Component
{
    /**
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

    /**
     * The type of the favorite section.
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
     * The 'See All' url string.
     *
     * @var string $seeAllURL
     */
    public string $seeAllURL;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param User $user
     * @param string $type
     * @return void
     */
    public function mount(User $user, string $type): void
    {
        $this->user = $user;
        $this->type = $type;
        $this->title = match ($type) {
            Anime::class => __('Favorite Anime'),
            Game::class => __('Favorite Games'),
            Manga::class => __('Favorite Manga'),
        };
        $this->seeAllURL = match ($type) {
            Anime::class => route('profile.favorite-anime', $user),
            Game::class => route('profile.favorite-games', $user),
            Manga::class => route('profile.favorite-manga', $user),
        };
    }

    /**
     * Sets the property to load the section.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->readyToLoad = true;
    }


    /**
     * Returns the user's feed messages.
     *
     * @return Collection
     */
    public function getFavoritesProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->user->whereFavorited($this->type)
            ->with(['genres', 'themes', 'media', 'mediaStat', 'translations', 'tv_rating'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.user.favorites-section');
    }
}
