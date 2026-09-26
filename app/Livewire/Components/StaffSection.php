<?php

namespace App\Livewire\Components;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Isolate]
#[Lazy]
class StaffSection extends Component
{
    /**
     * The library kind being viewed.
     *
     * @var int $kind
     */
    public int $kind = UserLibraryKind::Anime;

    /**
     * Anime parent populated by Livewire's blade-attribute binding when the kind is Anime.
     *
     * @var Anime|null $anime
     */
    public ?Anime $anime = null;

    /**
     * Manga parent populated by Livewire's blade-attribute binding when the kind is Manga.
     *
     * @var Manga|null $manga
     */
    public ?Manga $manga = null;

    /**
     * Game parent populated by Livewire's blade-attribute binding when the kind is Game.
     *
     * @var Game|null $game
     */
    public ?Game $game = null;

    /**
     * Prepare the component.
     *
     * @param int $kind
     *
     * @return void
     */
    public function mount(int $kind): void
    {
        $this->kind = $kind;

        $parent = $this->parent;
        $translation = $parent->translation;

        $stripped = $parent->withoutRelations()
            ->setRelation('translation', $translation);

        match ($this->kind) {
            UserLibraryKind::Anime => $this->anime = $stripped,
            UserLibraryKind::Manga => $this->manga = $stripped,
            UserLibraryKind::Game  => $this->game = $stripped,
        };
    }

    /**
     * Resolves the active parent model for the current kind.
     *
     * @return Anime|Manga|Game
     */
    public function getParentProperty(): Anime|Manga|Game
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => $this->anime,
            UserLibraryKind::Manga => $this->manga,
            UserLibraryKind::Game  => $this->game,
        };
    }

    /**
     * The skeleton shown until the section resumes loading.
     *
     * @return View
     */
    public function placeholder(): View
    {
        return view('components.skeletons.section', ['lockup' => 'person']);
    }

    /**
     * Loads the media staff section.
     *
     * @return Collection
     */
    public function getMediaStaffProperty(): Collection
    {
        return $this->parent->mediaStaff()
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
     * Returns the route name used by the "See All" link for the active kind.
     *
     * @return string
     */
    public function getSeeAllUrlProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => route('anime.staff', $this->parent),
            UserLibraryKind::Manga => route('manga.staff', $this->parent),
            UserLibraryKind::Game  => route('games.staff', $this->parent),
        };
    }

    /**
     * Returns the maximum number of relationships to load for the active kind.
     *
     * @return int
     */
    protected function maximumLimit(): int
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => Anime::MAXIMUM_RELATIONSHIPS_LIMIT,
            UserLibraryKind::Manga => Manga::MAXIMUM_RELATIONSHIPS_LIMIT,
            UserLibraryKind::Game  => Game::MAXIMUM_RELATIONSHIPS_LIMIT,
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.components.staff-section');
    }
}
