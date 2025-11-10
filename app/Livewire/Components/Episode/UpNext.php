<?php

namespace App\Livewire\Components\Episode;

use App\Models\Episode;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate]
class UpNext extends Component
{
    /**
     * The object containing the next episode data.
     *
     * @var Episode $nextEpisode
     */
    public Episode $nextEpisode;

    /**
     * Prepare the component.
     *
     * @param Episode $nextEpisode
     *
     * @return void
     */
    public function mount(Episode $nextEpisode): void
    {
        $this->nextEpisode = $nextEpisode
            ->loadMissing([
                'media',
                'anime' => function (HasOneThrough $hasOneThrough) {
                    $hasOneThrough->withoutGlobalScopes()
                        ->with([
                            'translation',
                        ]);
                },
                'season' => function (BelongsTo $query) {
                    $query->withoutGlobalScopes();
                }
            ])
            ->when(auth()->user(), function ($query, $user) use ($nextEpisode) {
                return $nextEpisode->loadExists([
                    'user_watched_episodes as isWatched' => function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    },
                ]);
            }, function () use ($nextEpisode) {
                return $nextEpisode;
            });
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.episode.up-next');
    }
}
