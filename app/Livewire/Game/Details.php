<?php

namespace App\Livewire\Game;

use App\Events\ModelViewed;
use App\Models\Game;
use App\Models\MediaRating;
use App\Models\Studio;
use App\Models\UserLibrary;
use App\Traits\Livewire\WithReviewBox;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Details extends Component
{
    use WithReviewBox;

    /**
     * The object containing the game data.
     *
     * @var Game $game
     */
    public Game $game;

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
     * Whether to show the popup to the user.
     *
     * @var bool $showPopup
     */
    public bool $showPopup = false;

    /**
     * The data used to populate the popup.
     *
     * @var array|string[]
     */
    public array $popupData = [
        'title' => '',
        'message' => '',
    ];

    /**
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

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

        $this->game = $game->loadMissing(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'themes', 'translations', 'status', 'tv_rating', 'country_of_origin'])
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

        if ($user = auth()->user()) {
            $this->game->setRelation('library', UserLibrary::where([
                ['trackable_type', '=', $game->getMorphClass()],
                ['trackable_id', '=', $game->id],
                ['user_id', '=', $user->id],
            ])->get());
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
        $this->showPopup = true;
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
                $this->popupData = [
                    'title' => __('Are you tracking?'),
                    'message' => __('Make sure to add the game to your library first.'),
                ];
                $this->showPopup = true;
            }
        } else {
            $this->popupData = [
                'title' => __('That’s Unfortunate'),
                'message' => __('This feature is only accessible to pro users 🧐'),
            ];
            $this->showPopup = true;
        }
    }

    /**
     * Returns the studio relationship of the game.
     *
     * @return Studio|null
     */
    public function getStudioProperty(): ?Studio
    {
        if (!$this->readyToLoad) {
            return null;
        }

        return $this->game->studios()?->firstWhere('is_studio', '=', true) ?? $this->game->studios->first();
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
