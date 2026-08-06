<?php

namespace App\Livewire\Episode;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\UserWatchedEpisode;
use App\Services\ScrobbleService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Livewire\Attributes\Locked;
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
    #[Locked]
    public bool $hasWatched;

    /**
     * Prepare the component.
     *
     * @param Episode $episode
     *
     * @return void
     */
    public function mount(Episode $episode): void
    {
        $this->episode = $episode;

        // Set watch status, else default to "disabled"
        $this->hasWatched = $episode->isWatched ?? false;
    }

    /**
     * Hydrate the episode.
     *
     * @return void
     */
    public function hydrateEpisode():void
    {
        $this->episode->load([
            'anime' => function (HasOneThrough $hasOneThrough) {
                $hasOneThrough->withoutGlobalScopes()
                    ->with([
                        'translation',
                    ]);
            },
        ]);
    }

    /**
     * Marks the episode as (un)watched.
     *
     * @param ScrobbleService $scrobbleService
     *
     * @return Application|RedirectResponse|Redirector|null
     */
    public function updateWatchStatus(ScrobbleService $scrobbleService)
    {
        $user = auth()->user();

        // Require user to authenticate if necessary.
        if (empty($user)) {
            return redirect(route('sign-in'));
        }

        // Find if the user has watched the episode
        $isAlreadyWatched = $user->hasWatched($this->episode);

        // If the episode's current status is watched then detach (unwatch) it, otherwise attach (watch) it.
        if ($isAlreadyWatched) {
            $user->episodes()->withoutGlobalScopes()->detach($this->episode);
            $this->hasWatched = false;
        } else {
            // Marking watched implies tracking; add the anime as in progress when absent.
            $anime = $this->episode->anime()->withoutGlobalScopes()
                ->select([Anime::TABLE_NAME . '.id'])
                ->first();
            $scrobbleService->ensureTracked($user, $anime);

            // Create-or-update so an in-progress scrobble row upgrades to completed.
            $user->episodes()->withoutGlobalScopes()->syncWithoutDetaching([
                $this->episode->id => UserWatchedEpisode::completedAttributes(),
            ]);
            $this->hasWatched = true;
        }

        // attach/detach bypass model events, so bump state_version explicitly.
        $user->bumpStateVersion();

        // Notify other components of an update in the anime's data
        $this->dispatch('update-episode');
        $this->dispatch('update-episode')->to('season.watch-button');
        $this->dispatch('refresh-up-next-episodes');
        $this->dispatch('refresh-past-episodes');
        $this->dispatch('refresh-up-next-section')->to('components.episode.up-next-section');
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
