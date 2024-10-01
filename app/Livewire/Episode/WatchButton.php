<?php

namespace App\Livewire\Episode;

use App\Models\Episode;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Livewire\Component;

class WatchButton extends Component
{
    /**
     * The object containing the episode data.
     *
     * @var Episode $episode
     */
    public Episode $episode;

    /**
     * Whether the auth user has watched the episode.
     *
     * @var bool $hasWatched
     */
    public bool $hasWatched;

    /**
     * Prepare the component.
     *
     * @param Episode $episode
     * @return void
     */
    public function mount(Episode $episode): void
    {
        $this->episode = $episode;

        // Set watch status, else default to "disabled"
        $this->hasWatched = $episode->isWatched ?? false;
    }

    /**
     * Marks the episode as (un)watched.
     *
     * @return Application|RedirectResponse|Redirector|null
     */
    public function updateWatchStatus(): Application|RedirectResponse|Redirector|null
    {
        $user = auth()->user();

        // Require user to authenticate if necessary.
        if (empty($user)) {
            return redirect(route('sign-in'));
        }

        if ($user->cannot('mark_as_watched', $this->episode)) {
            return null;
        }

        // Find if the user has watched the episode
        $isAlreadyWatched = $user->hasWatched($this->episode);

        // If the episode's current status is watched then detach (unwatch) it, otherwise attach (watch) it.
        if ($isAlreadyWatched) {
            $user->episodes()->detach($this->episode);
            $this->hasWatched = false;
        } else {
            $user->episodes()->attach($this->episode);
            $this->hasWatched = true;
        }

        // Notify other components of an update in the anime's data
        $this->dispatch('update-episode');
        $this->dispatch('update-episode')->to('season.watch-button');
        $this->dispatch('refresh-up-next-episodes');
        $this->dispatch('refresh-up-next-section')->to('components.episode.up-next-section');

        return null;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.episode.watch-button');
    }
}
