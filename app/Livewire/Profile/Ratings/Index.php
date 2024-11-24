<?php

namespace App\Livewire\Profile\Ratings;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaSong;
use App\Models\Person;
use App\Models\Studio;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    /**
     * The object containing the user data.
     *
     * @var User $user
     */
    public User $user;

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
     *
     * @return void
     */
    public function mount(User $user): void
    {
        $this->user = $user;
    }

    /**
     * Sets the property to load the page.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * The user's media ratings list.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getMediaRatingsProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        // We're aliasing `cursorName` as `rgc`, and setting
        // query rule to never show `cursor` param when it's
        // empty. Since `cursor` is also aliased as `rgc` in
        // query rules, and we always keep it empty, as far
        // as Livewire is concerned, `rgc` is also empty. So,
        // `rgc` doesn't show up in the query params in the
        // browser.
        return $this->user->mediaRatings()
            ->with([
                'model' => function (MorphTo $morphTo) {
                    $morphTo->constrain([
                        Anime::class => function (Builder $query) {
                            $query->with(['media', 'translation']);
                        },
                        Character::class => function (Builder $query) {
                            $query->with(['media']);
                        },
                        Episode::class => function (Builder $query) {
                            $query->with(['media', 'translation']);
                        },
                        Game::class => function (Builder $query) {
                            $query->with(['media', 'translation']);
                        },
                        Manga::class => function (Builder $query) {
                            $query->with(['media', 'translation']);
                        },
                        Person::class => function (Builder $query) {
                            $query->with(['media']);
                        },
                        Studio::class => function (Builder $query) {
                            $query->with(['media']);
                        },
                        MediaSong::class => function (Builder $query) {
                            $query->with([
                                'song' => function ($query) {
                                    $query->with(['media']);
                                },
                            ]);
                        },
                    ]);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(25);
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.ratings.index');
    }
}
