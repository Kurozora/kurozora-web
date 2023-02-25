<?php

namespace App\Http\Livewire\Game;

use App\Events\GameViewed;
use App\Models\Game;
use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the game data.
     *
     * @var Game $game
     */
    public Game $game;

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
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'update-game' => 'updateGameHandler'
    ];

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
     * Prepare the component.
     *
     * @param Game $game
     *
     * @return void
     */
    public function mount(Game $game): void
    {
        // Call the GameViewed event
        GameViewed::dispatch($game);

        $this->game = $game;
        $this->setupActions();
    }

    /**
     * Sets up the actions according to the user's settings.
     */
    protected function setupActions()
    {
        $user = auth()->user();
        if (!empty($user)) {
            $this->isTracking = $user->hasTracked($this->game);
            $this->isFavorited = $user->hasFavorited($this->game);
//            $this->isReminded = $user->reminderGame()->where('game_id', $this->game->id)->exists();
        }
    }

    /**
     * Handles the update game vent.
     */
    public function updateGameHandler()
    {
        $this->setupActions();
    }

    /**
     * Shows the trailer video to the user.
     */
    public function showVideo()
    {
        $this->showVideo = true;
        $this->showPopup = true;
    }

    /**
     * Adds the game to the user's favorite list.
     */
    public function favoriteGame()
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
    public function remindGame()
    {
        $user = auth()->user();

        if ($user->is_pro) {
            if ($this->isTracking) {
                if ($this->isReminded) { // Don't remind the user
                    $user->reminderGame()->detach($this->game->id);
                } else { // Remind the user
                    $user->reminderGame()->attach($this->game->id);
                }

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
