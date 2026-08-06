<?php

namespace App\Livewire\Season;

use App\Models\Anime;
use App\Models\Season;
use App\Models\UserWatchedEpisode;
use App\Services\ScrobbleService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class WatchButton extends Component
{
    /**
     * The object containing the episode data.
     *
     * @var Season $season
     */
    public Season $season;

    /**
     * Whether the auth user has watched the episode.
     *
     * @var bool $hasWatched
     */
    public bool $hasWatched;

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'update-episode' => 'setHasWatchedStatus'
    ];

    /**
     * Prepare the component.
     *
     * @param Season $season
     * @return void
     */
    public function mount(Season $season): void
    {
        $this->season = $season;

        // Set watch status
        $this->setHasWatchedStatus();
    }

    public function setHasWatchedStatus(): void
    {
        $this->hasWatched = auth()->user()?->hasWatchedSeason($this->season) ?? false;
    }

    /**
     * Marks the season as (un)watched.
     *
     * @param ScrobbleService $scrobbleService
     *
     * @return null|Redirector
     */
    public function updateWatchStatus(ScrobbleService $scrobbleService): ?Redirector
    {
        $user = auth()->user();

        // Require user to authenticate if necessary.
        if (empty($user)) {
            return to_route('sign-in');
        }

        // Get episode IDs
        $episodeIDs = $this->season->episodes()->pluck('id');

        // Find if the user has watched the season
        $isAlreadyWatched = $user->hasWatchedSeason($this->season);

        // If the season's current status is watched then detach (unwatch) it, otherwise attach (watch) it.
        if ($isAlreadyWatched) {
            $user->episodes()->detach($episodeIDs);
            $this->hasWatched = false;
        } else {
            // Marking watched implies tracking; add the anime as in progress when absent.
            $anime = $this->season->anime()->withoutGlobalScopes()
                ->select([Anime::TABLE_NAME . '.id'])
                ->first();
            $scrobbleService->ensureTracked($user, $anime);

            $existingIDs = $user->episodes()
                ->whereIn('episode_id', $episodeIDs)
                ->pluck('episode_id');
            $diffedEpisodeIDs = $episodeIDs->diff($existingIDs);

            $user->episodes()->attach($diffedEpisodeIDs, UserWatchedEpisode::completedAttributes());

            // Upgrade in-progress scrobble rows without touching already completed ones.
            $user->userWatchedEpisodes()
                ->whereIn('episode_id', $episodeIDs)
                ->whereNull('completed_at')
                ->update(UserWatchedEpisode::completedAttributes());

            $this->hasWatched = true;
        }

        // attach/detach bypass model events, so bump state_version explicitly.
        $user->bumpStateVersion();

        // Notify other components of an update in the anime's data
        $this->dispatch('update-season');

        return null;
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.season.watch-button');
    }
}
