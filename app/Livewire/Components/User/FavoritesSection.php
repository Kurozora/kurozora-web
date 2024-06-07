<?php

namespace App\Livewire\Components\User;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
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
            Anime::class => route('profile.anime.favorites', $user),
            Game::class => route('profile.games.favorites', $user),
            Manga::class => route('profile.manga.favorites', $user),
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
            ->with(['genres', 'media', 'mediaStat', 'themes', 'translations', 'tv_rating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
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
