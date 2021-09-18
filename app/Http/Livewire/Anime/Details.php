<?php

namespace App\Http\Livewire\Anime;

use App\Models\Anime;
use Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * Whether the user has favorited the anime.
     *
     * @var bool $isFavorited
     */
    public bool $isFavorited = false;

    /**
     * Whether the user is reminded of the anime.
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
        'update-anime' => 'updateAnimeHandler'
    ];

    /**
     * Whether the user is tracking the anime.
     *
     * @var bool $isTracking
     */
    public bool $isTracking = false;

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
     * @param Anime $anime
     *
     * @return void
     */
    public function mount(Anime $anime)
    {
        $this->anime = $anime;

        $this->setupActions();
    }

    /**
     * Sets up the actions according to the user's settings.
     */
    protected function setupActions()
    {
        $user = Auth::user();
        if (!empty($user)) {
            $this->isTracking = $user->isTracking($this->anime);
            $this->isFavorited = $user->favoriteAnime()->where('anime_id', $this->anime->id)->exists();
            $this->isReminded = $user->reminderAnime()->where('anime_id', $this->anime->id)->exists();
        }
    }

    /**
     * Handles the update anime vent.
     */
    public function updateAnimeHandler()
    {
        $this->setupActions();
    }

    /**
     * Adds the anime to the user's favorite list.
     */
    public function favoriteAnime()
    {
        $user = Auth::user();

        if ($this->isTracking) {
            if ($this->isFavorited) { // Unfavorite the show
                $user->favoriteAnime()->detach($this->anime->id);
            } else { // Favorite the show
                $user->favoriteAnime()->attach($this->anime->id);
            }

            $this->isFavorited = !$this->isFavorited;
        }
    }

    /**
     * Adds the anime to the user's reminder list.
     */
    public function remindAnime()
    {
        $user = Auth::user();

        if (empty($user->receipt) || !$user->receipt->is_subscribed ?? true) {
            $this->popupData = [
                'title' => __('That’s Unfortunate'),
                'message' => __('This feature is only accessible to pro users 🧐'),
            ];
            $this->showPopup = true;
        } else {
            if ($this->isTracking) {
                if ($this->isReminded) { // Don't remind the user
                    $user->reminderAnime()->detach($this->anime->id);
                } else { // Remind the user
                    $user->reminderAnime()->attach($this->anime->id);
                }

                $this->isReminded = !$this->isReminded;
            }
        }
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Factory|View|Application
    {
        return view('livewire.anime.details')
            ->layout('layouts.base');
    }
}
