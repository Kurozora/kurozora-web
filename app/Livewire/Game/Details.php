<?php

namespace App\Livewire\Game;

use App\Enums\UserLibraryStatus;
use App\Events\ModelViewed;
use App\Models\Game;
use App\Models\MediaRating;
use App\Models\Studio;
use App\Models\UserLibrary;
use App\Traits\Livewire\PresentsAlert;
use App\Traits\Livewire\WithReviewBox;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Details extends Component
{
    use PresentsAlert,
        WithReviewBox;

    /**
     * The object containing the game data.
     *
     * @var Game $game
     */
    public Game $game;

    /**
     * The object containing the studio data.
     *
     * @var ?Studio $studio
     */
    public ?Studio $studio;

    /**
     * The object containing the user's rating data.
     *
     * @var Collection|MediaRating[] $userRating
     */
    public Collection|array $userRating;

    /**
     * The object containing the user's library data.
     *
     * @var Collection|UserLibrary[] $library
     */
    public Collection|array $library;

    /**
     * Whether the user has favorited the game.
     *
     * @var bool $isFavorited
     */
    public bool $isFavorited = false;

    /**
     * Whether the user is reminded of the game.
     *
     * @var bool $isReminded
     */
    public bool $isReminded = false;

    /**
     * Whether the user is tracking the game.
     *
     * @var bool $isTracking
     */
    public bool $isTracking = false;

    /**
     * Whether to show the video to the user.
     *
     * @var bool $showVideo
     */
    public bool $showVideo = false;

    /**
     * Whether to show the add-to-library modal to the user.
     *
     * @var bool $showAddToLibrary
     */
    public bool $showAddToLibrary = false;

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * The addition status.
     *
     * @var string
     */
    public string $addStatus = '';

    /**
     * The query strings of the component.
     *
     * @return string[][]
     */
    protected function queryString(): array
    {
        return [
            'addStatus' => ['as' => 'add_to_library', 'except' => ''],
        ];
    }

    /**
     * Prepare the component.
     *
     * @param Game $game
     *
     * @return void
     */
    public function mount(Game $game): void
    {
        // Call the ModelViewed event
        ModelViewed::dispatch($game, request()->ip());

        $this->game = $game->loadMissing([
            'genres',
            'languages',
            'media',
            'mediaStat',
            'media_type',
            'themes',
            'translation',
            'status',
            'tv_rating',
            'country_of_origin',
            'studios' => function (BelongsToMany $query) {
                $query->withoutGlobalScopes()
                    ->orderByRaw('CASE WHEN is_studio = true THEN 0 ELSE 1 END')
                    ->limit(1);
            },
        ])
            ->when(auth()->user(), function ($query, $user) use ($game) {
                return $game->loadMissing(['mediaRatings' => function ($query) {
                    $query->where('user_id', '=', auth()->user()->id);
                }])
                    ->loadExists([
                        'favoriters as isFavorited' => function ($query) use ($user) {
                            $query->where('user_id', '=', $user->id);
                        },
//                        'reminderers as isReminded' => function ($query) use ($user) {
//                            $query->where('user_id', '=', $user->id);
//                        },
                    ]);
            }, function() use ($game) {
                return $game;
            });
        $this->studio = $game->studios->first();

        if ($user = auth()->user()) {
            $this->game->setRelation('library', UserLibrary::where([
                ['trackable_type', '=', $game->getMorphClass()],
                ['trackable_id', '=', $game->id],
                ['user_id', '=', $user->id],
            ])->get());

            // Determine whether to show the add-to-library modal
            if ($game->library->isEmpty()) {
                try {
                    $addStatus = str($this->addStatus)
                        ->title()
                        ->replace('-', '');

                    if (UserLibraryStatus::fromKey($addStatus)) {
                        $this->showAddToLibrary = true;
                    }
                } catch (Exception) {}
            }
        } else {
            $this->game->setRelation('library', collect());
            $this->game->setRelation('mediaRatings', collect());
        }

        $this->isFavorited = (bool) $game->isFavorited;
//        $this->isReminded = (bool) $game->isReminded;
        $this->isTracking = $game->library->isNotEmpty();
        $this->userRating = $game->mediaRatings;
        $this->library = $game->library;
    }

    public function dehydrateGame($value): void
    {
        // For some reason the library relation isn't hydrated correctly.
        // The relation is hydrated without the `where` constraint on the
        // user's ID. So it hydrates all UserLibrary models from the database
        // for the given model. Bad performance. The fix is to unset the
        // relation here, then set it back in the hydrate method.
        $value->unsetRelation('library');
        $value->unsetRelation('mediaRatings');
    }

    public function hydrateGame($value): void
    {
        $value->setRelation('library', $this->library);
        $value->setRelation('mediaRatings', $this->userRating);
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
     * Shows the trailer video to the user.
     */
    public function showTrailerVideo(): void
    {
        $this->showVideo = true;
    }

    /**
     * Add the model to the user's library.
     */
    #[Renderless]
    public function addToLibrary(): void
    {
        $addStatus = str($this->addStatus)
            ->title()
            ->replace('-', '');
        $libraryStatus = UserLibraryStatus::fromKey($addStatus);

        // Update or create the user library entry.
        UserLibrary::withoutSyncingToSearch(function () use($libraryStatus) {
            $userLibrary = UserLibrary::updateOrCreate([
                'user_id' => auth()->id(),
                'trackable_type' => $this->game->getMorphClass(),
                'trackable_id' => $this->game->id,
            ], [
                'status' => $libraryStatus->value,
            ]);

            $userLibrary->setRelation('trackable', $this->game);

            $userLibrary->searchable();
        });

        $this->dispatch('update-library-status', modelType: $this->game->getMorphClass(), modelID: $this->game->id, libraryStatus: $libraryStatus->value)
            ->to('components.library-button');
        $this->dismissAddToLibrary();
    }

    /**
     * Handle the dismissing of the add-to-library modal.
     *
     * @return void
     */
    public function dismissAddToLibrary(): void
    {
        $this->showAddToLibrary = false;
        $this->addStatus = '';
    }

    /**
     * Adds the game to the user's favorite list.
     */
    public function favoriteGame(): void
    {
        $user = auth()->user();

        if ($this->isTracking) {
            if ($this->isFavorited) { // Unfavorite the show
                $user->unfavorite($this->game);
            } else { // Favorite the show
                $user->favorite($this->game);
            }

            $this->isFavorited = !$this->isFavorited;
        }
    }

    /**
     * Adds the game to the user's reminder list.
     */
    public function remindGame(): void
    {
        $user = auth()->user();

        if ($user->is_subscribed) {
            if ($this->isTracking) {
//                if ($this->isReminded) { // Don't remind the user
//                    $user->reminderGame()->detach($this->game->id);
//                } else { // Remind the user
//                    $user->reminderGame()->attach($this->game->id);
//                }

                $this->isReminded = !$this->isReminded;
            } else {
                $this->presentAlert(
                    title: __('Are you tracking?'),
                    message: __('Make sure to add the game to your library first.')
                );
            }
        } else {
            $this->presentAlert(
                title: __('That’s unfortunate'),
                message: __('Reminders are only available to pro and subscribed users 🧐'),
            );
        }
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.game.details');
    }
}
