<?php

namespace App\Http\Livewire\Profile\Reviews;

use App\Models\Anime;
use App\Models\Character;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaSong;
use App\Models\Person;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Pagination\CursorPaginator;
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
     * The current page query parameter's alias.
     *
     * @var string $rwc
     */
    public string $rwc = '';

    /**
     * The current page query parameter.
     *
     * @var string $cursor
     */
    public string $cursor = '';

    /**
     * The query strings of the component.
     *
     * @var string[] $queryString
     */
    protected $queryString = [
        'cursor' => ['except' => '', 'as' => 'rwc']
    ];

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
     * The user's media ratings list.
     *
     * @return CursorPaginator
     */
    public function getMediaRatingsProperty(): CursorPaginator
    {
        // We're aliasing `cursorName` as `rwc`, and setting
        // query rule to never show `cursor` param when it's
        // empty. Since `cursor` is also aliased as `rwc` in
        // query rules, and we always keep it empty, as far
        // as Livewire is concerned, `rwc` is also empty. So,
        // `rwc` doesn't show up in the query params in the
        // browser.
        return $this->user->mediaRatings()
            ->with([
                'model' => function (MorphTo $morphTo) {
                    $morphTo->constrain([
                        Anime::class => function (Builder $query) {
                            $query->with(['media', 'translations']);
                        },
                        Character::class => function (Builder $query) {
                            $query->with(['media']);
                        },
                        Game::class => function (Builder $query) {
                            $query->with(['media', 'translations']);
                        },
                        Manga::class => function (Builder $query) {
                            $query->with(['media', 'translations']);
                        },
                        Person::class => function (Builder $query) {
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
            ->cursorPaginate(25, ['*'], 'rwc');
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.profile.reviews.index');
    }
}
