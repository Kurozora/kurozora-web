<?php

namespace App\View\Components\Episode;

use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\Component;

class UpNextEpisodes extends Component
{
    /**
     * The current page of episodes to watch next.
     *
     * @var LengthAwarePaginator $episodes
     */
    public LengthAwarePaginator $episodes;

    /**
     * The URL that re-renders the section.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $user = auth()->user();

        $this->episodes = $user->up_next_episodes()
            ->withExists([
                'userWatchedEpisodes as isWatched' => function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->completed();
                },
            ])
            ->paginate(25);
        $this->refreshUrl = route('up-next.section', ['episodes', 'page' => request()->query('page')], false);
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.episode.up-next-episodes');
    }
}
