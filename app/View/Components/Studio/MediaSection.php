<?php

namespace App\View\Components\Studio;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Studio;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class MediaSection extends Component
{
    /**
     * The studio whose works are shown.
     *
     * @var Studio $studio
     */
    public Studio $studio;

    /**
     * The class of the shown works.
     *
     * @var string $type
     */
    public string $type;

    /**
     * The title of the section.
     *
     * @var string $title
     */
    public string $title;

    /**
     * The URL of the full list.
     *
     * @var string $seeAllUrl
     */
    public string $seeAllUrl;

    /**
     * The URL that re-renders the section.
     *
     * @var string $refreshUrl
     */
    public string $refreshUrl;

    /**
     * The studio's works.
     *
     * @var Collection $models
     */
    public Collection $models;

    /**
     * Create a new component instance.
     *
     * @param Studio $studio
     * @param string $type
     */
    public function __construct(Studio $studio, string $type)
    {
        $this->studio = $studio;
        $this->type = $type;
        $this->title = match ($type) {
            Anime::class => __('Anime'),
            Manga::class => __('Manga'),
            Game::class => __('Games'),
        };
        $this->seeAllUrl = match ($type) {
            Anime::class => route('studios.anime', $studio),
            Manga::class => route('studios.manga', $studio),
            Game::class => route('studios.games', $studio),
        };
        $this->refreshUrl = match ($type) {
            Anime::class => route('studios.section', [$studio, 'anime'], false),
            Manga::class => route('studios.section', [$studio, 'manga'], false),
            Game::class => route('studios.section', [$studio, 'games'], false),
        };
        $this->models = $this->loadModels();
    }

    /**
     * Whether the component should be rendered.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->models->isNotEmpty();
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.studio.media-section');
    }

    /**
     * Load the studio's works.
     *
     * @return Collection
     */
    protected function loadModels(): Collection
    {
        $query = match ($this->type) {
            Anime::class => $this->studio->anime(),
            Manga::class => $this->studio->manga(),
            Game::class => $this->studio->games(),
        };

        return $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tvRating'])
            ->when(auth()->user(), function ($query, $user) {
                $query->with(['library' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->id);
                }]);
            })
            ->limit(Studio::MAXIMUM_RELATIONSHIPS_LIMIT)
            ->get();
    }
}
