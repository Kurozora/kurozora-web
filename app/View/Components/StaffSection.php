<?php

namespace App\View\Components;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class StaffSection extends Component
{
    /**
     * The library kind being viewed.
     *
     * @var int $kind
     */
    public int $kind;

    /**
     * The anime, manga, or game whose staff is shown.
     *
     * @var Anime|Manga|Game $model
     */
    public Anime|Manga|Game $model;

    /**
     * The staff entries shown in the section.
     *
     * @var Collection $mediaStaff
     */
    public Collection $mediaStaff;

    /**
     * The URL of the full staff page.
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
        $this->mediaStaff = $this->loadMediaStaff();
        $this->seeAllUrl = match ($kind) {
            UserLibraryKind::Anime => route('anime.staff', $model),
            UserLibraryKind::Manga => route('manga.staff', $model),
            UserLibraryKind::Game => route('games.staff', $model),
        };
        $this->refreshUrl = match ($kind) {
            UserLibraryKind::Anime => route('anime.section', [$model, 'staff'], false),
            UserLibraryKind::Manga => route('manga.section', [$model, 'staff'], false),
            UserLibraryKind::Game => route('games.section', [$model, 'staff'], false),
        };
    }

    /**
     * Whether the section has staff to show.
     *
     * @return bool
     */
    public function shouldRender(): bool
    {
        return $this->mediaStaff->isNotEmpty();
    }

    /**
     * Get the view that represents the component.
     *
     * @return View
     */
    public function render(): View
    {
        return view('components.staff-section');
    }

    /**
     * Loads the staff entries for the active kind.
     *
     * @return Collection
     */
    protected function loadMediaStaff(): Collection
    {
        return $this->model->mediaStaff()
            ->with([
                'person' => function ($query) {
                    $query->with(['media']);
                },
                'staffRole'
            ])
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
