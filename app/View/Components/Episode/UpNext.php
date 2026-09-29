<?php

namespace App\View\Components\Episode;

use App\Models\Episode;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\View\Component;

class UpNext extends Component
{
    /**
     * The episode that airs next.
     *
     * @var Episode $nextEpisode
     */
    public Episode $nextEpisode;

    /**
     * Create a new component instance.
     *
     * @param Episode $nextEpisode
     */
    public function __construct(Episode $nextEpisode)
    {
        $this->nextEpisode = $nextEpisode->loadMissing([
            'media',
            'anime' => function (HasOneThrough $hasOneThrough) {
                $hasOneThrough->withoutGlobalScopes()
                    ->with(['translation']);
            },
            'season' => function (BelongsTo $query) {
                $query->withoutGlobalScopes();
            },
        ]);

        if ($user = auth()->user()) {
            $this->nextEpisode->loadExists([
                'userWatchedEpisodes as isWatched' => function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->completed();
                },
            ]);
        }
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.episode.up-next');
    }
}
