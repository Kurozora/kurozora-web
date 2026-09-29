<?php

namespace App\View\Components;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class SongsSection extends Component
{
    /**
     * The library kind being viewed.
     *
     * @var int $kind
     */
    public int $kind;

    /**
     * The anime or game whose songs are shown.
     *
     * @var Anime|Game $model
     */
    public Anime|Game $model;

    /**
     * The songs shown in the section.
     *
     * @var Collection $mediaSongs
     */
    public Collection $mediaSongs;

    /**
     * The URL of the full songs page.
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
     * @param int        $kind
     * @param Anime|Game $model
     */
    public function __construct(int $kind, Anime|Game $model)
    {
        $this->kind = $kind;
        $this->model = $model;
        $this->mediaSongs = $this->loadMediaSongs();
        $this->seeAllUrl = match ($kind) {
            UserLibraryKind::Anime => route('anime.songs', $model),
            UserLibraryKind::Game => route('games.songs', $model),
        };
        $this->refreshUrl = match ($kind) {
            UserLibraryKind::Anime => route('anime.section', [$model, 'songs'], false),
            UserLibraryKind::Game => route('games.section', [$model, 'songs'], false),
        };
    }

    /**
     * Whether the section has songs to show.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->mediaSongs->isNotEmpty();
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.songs-section');
    }

    /**
     * Loads the songs for the active kind.
     *
     * @return Collection
     */
    protected function loadMediaSongs(): Collection
    {
        return $this->model->mediaSongs()
            ->with([
                'song' => function ($query) {
                    $query->with(['media']);
                }
            ])
            ->limit($this->maximumLimit())
            ->orderBy('position')
            ->get();
    }

    /**
     * The maximum number of relationships to load for the active kind.
     *
     * @return int
     */
    protected function maximumLimit(): int
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => Anime::MAXIMUM_RELATIONSHIPS_LIMIT,
            UserLibraryKind::Game => Game::MAXIMUM_RELATIONSHIPS_LIMIT,
        };
    }
}
