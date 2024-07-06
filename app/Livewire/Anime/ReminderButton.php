<?php

namespace App\Livewire\Anime;

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
    public function mount(Anime $anime): void
    {
        $this->anime = $anime;

        $this->isTracking = $anime->library->isNotEmpty();
        $this->isReminded = (bool) $anime->isReminded;
    }

    /**
     * Adds the anime to the user's reminder list, and Planning library list
     * if not tracked already.
     */
    public function remindAnime(): void
    {
        if ($this->isReminded) {
            return;
        }

        $user = auth()->user();

        if (!$user->is_subscribed) {
            return;
        }

        if (!$this->isTracking) {
            $user->track($this->anime, UserLibraryStatus::Planning());
        }

        $user->remind($this->anime);
        $this->isReminded = true;
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
