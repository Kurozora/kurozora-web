<?php

namespace App\Http\Livewire\Anime;

use App\Events\AnimeViewed;
use App\Models\Anime;
use App\Models\Studio;
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
     * The object containing the studio data.
     *
     * @var Studio|null
     */
    public ?Studio $studio = null;

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
     * @param Anime $anime
     *
     * @return void
     */
    public function mount(Anime $anime): void
    {
        // Call the AnimeViewed event
        AnimeViewed::dispatch($anime);

        $this->anime = $anime;
        $this->studio = $anime->studios()?->firstWhere('is_studio', '=', true) ?? $anime->studios->first();
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
            $this->isFavorited = $user->favorite_anime()->where('anime_id', $this->anime->id)->exists();
            $this->isReminded = $user->reminder_anime()->where('anime_id', $this->anime->id)->exists();
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
     * Shows the trailer video to the user.
     */
    public function showVideo()
    {
        $this->showVideo = true;
        $this->showPopup = true;
    }

    /**
     * Adds the anime to the user's favorite list.
     */
    public function favoriteAnime()
    {
        $user = Auth::user();

        if ($this->isTracking) {
            if ($this->isFavorited) { // Unfavorite the show
                $user->favorite_anime()->detach($this->anime->id);
            } else { // Favorite the show
                $user->favorite_anime()->attach($this->anime->id);
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

        if ($user->isPro()) {
            if ($this->isTracking) {
                if ($this->isReminded) { // Don't remind the user
                    $user->reminder_anime()->detach($this->anime->id);
                } else { // Remind the user
                    $user->reminder_anime()->attach($this->anime->id);
                }

                $this->isReminded = !$this->isReminded;
            } else {
                $this->popupData = [
                    'title' => __('Are you tracking?'),
                    'message' => __('Make sure to add the anime to your library first.'),
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
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.anime.details');
    }
}
