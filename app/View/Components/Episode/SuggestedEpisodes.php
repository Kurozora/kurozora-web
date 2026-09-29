<?php

namespace App\View\Components\Episode;

use App\Models\Episode;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class SuggestedEpisodes extends Component
{
    /**
     * The title the suggestions are searched by.
     *
     * @var string $title
     */
    public string $title;

    /**
     * The id of the episode airing next.
     *
     * @var int|string|null $nextEpisodeID
     */
    public int|string|null $nextEpisodeID;

    /**
     * The suggested episodes.
     *
     * @var Collection $episodes
     */
    public Collection $episodes;

    /**
     * Create a new component instance.
     *
     * @param string          $title
     * @param int|string|null $nextEpisodeId
     */
    public function __construct(string $title, int|string|null $nextEpisodeId = null)
    {
        $this->title = $title;
        $this->nextEpisodeID = $nextEpisodeId;
        $this->episodes = $this->loadEpisodes();
    }

    /**
     * Whether the component should be rendered.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->episodes->isNotEmpty();
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.episode.suggested-episodes');
    }

    /**
     * Load the suggested episodes.
     *
     * @return Collection
     */
    protected function loadEpisodes(): Collection
    {
        $title = trim(mb_substr($this->title, 0, 20, 'UTF-8'));

        if ($title === '') {
            return collect();
        }

        return Episode::search($title)
            ->take(10)
            ->query(function ($query) {
                $query->with([
                    'anime' => function ($query) {
                        $query->with([
                            'media',
                            'translation',
                        ]);
                    },
                    'media',
                    'season' => function ($query) {
                        $query->with(['translation']);
                    },
                    'translation',
                ])
                ->when(auth()->user(), function ($query, $user) {
                    $query->withExists([
                        'userWatchedEpisodes as isWatched' => function ($query) use ($user) {
                            $query->where('user_id', $user->id)
                                ->completed();
                        },
                    ]);
                });
            })
            ->get();
    }
}
