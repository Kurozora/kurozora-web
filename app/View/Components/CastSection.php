<?php

namespace App\View\Components;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class CastSection extends Component
{
    /**
     * The library kind being viewed.
     *
     * @var int $kind
     */
    public int $kind;

    /**
     * The anime, manga, or game whose cast is shown.
     *
     * @var Anime|Manga|Game $model
     */
    public Anime|Manga|Game $model;

    /**
     * The cast entries shown in the section.
     *
     * @var Collection $cast
     */
    public Collection $cast;

    /**
     * The URL of the full cast page.
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
     * @param int              $kind
     * @param Anime|Manga|Game $model
     */
    public function __construct(int $kind, Anime|Manga|Game $model)
    {
        $this->kind = $kind;
        $this->model = $model;
        $this->cast = $this->loadCast();
        $this->seeAllUrl = match ($kind) {
            UserLibraryKind::Anime => route('anime.cast', $model),
            UserLibraryKind::Manga => route('manga.cast', $model),
            UserLibraryKind::Game => route('games.cast', $model),
        };
        $this->refreshUrl = match ($kind) {
            UserLibraryKind::Anime => route('anime.section', [$model, 'cast'], false),
            UserLibraryKind::Manga => route('manga.section', [$model, 'cast'], false),
            UserLibraryKind::Game => route('games.section', [$model, 'cast'], false),
        };
    }

    /**
     * Whether the section has cast to show.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->cast->isNotEmpty();
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.cast-section');
    }

    /**
     * Loads the cast entries for the active kind.
     *
     * @return Collection
     */
    protected function loadCast(): Collection
    {
        $eagerLoads = match ($this->kind) {
            UserLibraryKind::Manga => [
                'character' => function ($query) {
                    $query->with(['media', 'translation']);
                },
                'castRole',
            ],
            default => [
                'person' => function ($query) {
                    $query->with(['media']);
                },
                'character' => function ($query) {
                    $query->with(['media', 'translation']);
                },
                'castRole',
            ],
        };

        return $this->model->cast()
            ->with($eagerLoads)
            ->limit($this->maximumLimit())
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
            UserLibraryKind::Manga => Manga::MAXIMUM_RELATIONSHIPS_LIMIT,
            UserLibraryKind::Game => Game::MAXIMUM_RELATIONSHIPS_LIMIT,
        };
    }
}
