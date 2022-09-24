<?php

namespace App\Http\Livewire\Anime;

use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ReminderButton extends Component
{
    /**
     * The object containing the anime data.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * Whether the button is disabled.
     *
     * @var bool $disabled
     */
    public bool $disabled = true;

    /**
     * Whether the user is tracking the anime.
     *
     * @var bool $isTracking
     */
    public bool $isTracking = false;

    /**
     * Whether the reminder is active.
     *
     * @var bool $isReminded
     */
    public bool $isReminded = false;

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
        $user = auth()->user();
        if (!empty($user)) {
            $this->isTracking = $user->isTracking($this->anime);
            $this->isReminded = $user->reminder_anime()->where('anime_id', $this->anime->id)->exists();
            $this->disabled = $this->isReminded;
        }
    }

    /**
     * Adds the anime to the user's reminder list, and Planning library list
     * if not tracked already.
     */
    public function remindAnime()
    {
        if (!$this->isReminded) {
            $user = auth()->user();

            if (!$user->isPro()) {
                if (!$this->isTracking) {
                    $user->library()->attach($this->anime->id, ['status' => UserLibraryStatus::Planning]);
                }

                $user->reminder_anime()->attach($this->anime->id);
                $this->isReminded = true;
                $this->disabled = true;
            }
        } else {
            dd('gotcha, you sneaky bastard');
        }
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.anime.reminder-button');
    }
}
