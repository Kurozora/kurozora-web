<?php

namespace App\View\Components;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class StudiosSection extends Component
{
    /**
     * The library kind being viewed.
     *
     * @var int $kind
     */
    public int $kind;

    /**
     * The anime, manga, or game whose studios are shown.
     *
     * @var Anime|Manga|Game $model
     */
    public Anime|Manga|Game $model;

    /**
     * The studios shown in the section.
     *
     * @var Collection $studios
     */
    public Collection $studios;

    /**
     * The URL of the full studios page.
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
        $this->studios = $this->loadStudios();
        $this->seeAllUrl = match ($kind) {
            UserLibraryKind::Anime => route('anime.studios', $model),
            UserLibraryKind::Manga => route('manga.studios', $model),
            UserLibraryKind::Game => route('games.studios', $model),
        };
        $this->refreshUrl = match ($kind) {
            UserLibraryKind::Anime => route('anime.section', [$model, 'studios'], false),
            UserLibraryKind::Manga => route('manga.section', [$model, 'studios'], false),
            UserLibraryKind::Game => route('games.section', [$model, 'studios'], false),
        };
    }

    /**
     * Whether the section has studios to show.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->studios->isNotEmpty();
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.studios-section');
    }

    /**
     * Loads the studios for the active kind.
     *
     * @return Collection
     */
    protected function loadStudios(): Collection
    {
        $model = $this->model;

        return $model->studios()
            ->when($model->tv_rating_id > request()->tvRating(), function ($query) {
                $query->withoutGlobalScopes();
            })
            ->with('media')
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
