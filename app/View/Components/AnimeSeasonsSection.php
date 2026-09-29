<?php

namespace App\View\Components;

use App\Models\Anime;
use App\Models\Season;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class AnimeSeasonsSection extends Component
{
    /**
     * The anime whose seasons are shown.
     *
     * @var Anime $anime
     */
    public Anime $anime;

    /**
     * The seasons shown in the section.
     *
     * @var Collection $seasons
     */
    public Collection $seasons;

    /**
     * The URL of the full seasons page.
     *
     * @var string $seeAllUrl
     */
    public string $seeAllUrl;

    /**
     * The URL that renders the section again.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * Create the component.
     *
     * @param Anime $anime
     */
    public function __construct(Anime $anime)
    {
        $this->anime = $anime;
        $this->seasons = $this->loadSeasons();
        $this->seeAllUrl = route('anime.seasons', $anime);
        $this->refreshUrl = route('anime.section', [$anime, 'seasons'], false);
    }

    /**
     * Whether the anime has seasons to show.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->seasons->isNotEmpty();
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.anime-seasons-section');
    }

    /**
     * Loads the anime's seasons.
     *
     * @return Collection
     */
    protected function loadSeasons(): Collection
    {
        return $this->anime->seasons()
            ->when($this->anime->tv_rating_id > request()->tvRating(), function ($query) {
                $query->withoutGlobalScopes();
            })
            ->with(['media', 'translation'])
            ->withCount([
                'episodes' => function ($query) {
                    $query->withoutGlobalScopes();
                }
            ])
            ->withAvg([
                'episodesMediaStats as rating_average' => function ($query) {
                    $query->withoutGlobalScopes()
                        ->where('rating_average', '!=', 0);
                }
            ], 'rating_average')
            ->orderBy('number', 'desc')
            ->limit(Anime::MAXIMUM_RELATIONSHIPS_LIMIT)
            ->get()
            ->map(function (Season $season) {
                return $season->setRelation('anime', $this->anime);
            });
    }
}
