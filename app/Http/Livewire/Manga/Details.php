<?php

namespace App\Http\Livewire\Manga;

use App\Events\MangaViewed;
use App\Models\Manga;
use App\Models\Studio;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Details extends Component
{
    /**
     * The object containing the manga data.
     *
     * @var Manga $manga
     */
    public Manga $manga;

    /**
     * Whether the user has favorited the manga.
     *
     * @var bool $isFavorited
     */
    public bool $isFavorited = false;

    /**
     * Whether the user is reminded of the manga.
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
        'update-manga' => 'updateMangaHandler'
    ];

    /**
     * Whether the user is tracking the manga.
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
     * @param Manga $manga
     *
     * @return void
     */
    public function mount(Manga $manga): void
    {
        // Call the MangaViewed event
        MangaViewed::dispatch($manga);

        $this->manga = $manga;
        $this->setupActions();
    }

    /**
     * Sets up the actions according to the user's settings.
     */
    protected function setupActions()
    {
        $user = auth()->user();
        if (!empty($user)) {
            $this->isTracking = $user->hasTracked($this->manga);
            $this->isFavorited = $user->hasFavorited($this->manga);
//            $this->isReminded = $user->reminderManga()->where('manga_id', $this->manga->id)->exists();
        }
    }

    /**
     * Handles the update manga vent.
     */
    public function updateMangaHandler()
    {
        $this->setupActions();
    }

    /**
     * Adds the manga to the user's favorite list.
     */
    public function favoriteManga()
    {
        $user = auth()->user();

        if ($this->isTracking) {
            if ($this->isFavorited) { // Unfavorite the show
                $user->unfavorite($this->manga);
            } else { // Favorite the show
                $user->favorite($this->manga);
            }

            $this->isFavorited = !$this->isFavorited;
        }
    }

    /**
     * Adds the manga to the user's reminder list.
     */
    public function remindManga()
    {
        $user = auth()->user();

        if ($user->is_pro) {
            if ($this->isTracking) {
//                if ($this->isReminded) { // Don't remind the user
//                    $user->reminderManga()->detach($this->manga->id);
//                } else { // Remind the user
//                    $user->reminderManga()->attach($this->manga->id);
//                }

                $this->isReminded = !$this->isReminded;
            } else {
                $this->popupData = [
                    'title' => __('Are you tracking?'),
                    'message' => __('Make sure to add the manga to your library first.'),
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
     * Returns the studio relationship of the manga.
     *
     * @return Studio|null
     */
    public function getStudioProperty(): ?Studio
    {
        return $this->manga->studios()?->firstWhere('is_studio', '=', true) ?? $this->manga->studios->first();
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.manga.details');
    }
}
