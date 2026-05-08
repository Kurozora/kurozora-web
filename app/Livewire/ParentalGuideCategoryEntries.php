<?php

namespace App\Livewire;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Traits\Livewire\ParentalGuideCategoryListing;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class ParentalGuideCategoryEntries extends Component
{
    use ParentalGuideCategoryListing;

    /**
     * The library kind being viewed.
     *
     * @var int $kind
     */
    public int $kind = UserLibraryKind::Anime;

    /**
     * Anime parent populated by Livewire's route binding when the kind is Anime.
     *
     * @var Anime|null $anime
     */
    public ?Anime $anime = null;

    /**
     * Manga parent populated by Livewire's route binding when the kind is Manga.
     *
     * @var Manga|null $manga
     */
    public ?Manga $manga = null;

    /**
     * Game parent populated by Livewire's route binding when the kind is Game.
     *
     * @var Game|null $game
     */
    public ?Game $game = null;

    /**
     * Prepare the component.
     *
     * @param int    $kind
     * @param string $category
     *
     * @return void
     */
    public function mount(int $kind, string $category): void
    {
        $this->kind = $kind;
        $this->parent->loadMissing(['media', 'translation']);
        $this->resolveCategoryFromSlug($category);
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
     * Returns the URL segment used for the appArgument slot.
     *
     * @return string
     */
    public function getAppArgumentSegmentProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => 'anime',
            UserLibraryKind::Manga => 'manga',
            UserLibraryKind::Game  => 'games',
        };
    }

    /**
     * Returns the canonical URL for the active kind's category listing page.
     *
     * @return string
     */
    public function getCanonicalUrlProperty(): string
    {
        $categorySlug = $this->category?->urlSlug() ?? '';

        return match ($this->kind) {
            UserLibraryKind::Anime => route('anime.parentalguide.category', ['anime' => $this->parent, 'category' => $categorySlug]),
            UserLibraryKind::Manga => route('manga.parentalguide.category', ['manga' => $this->parent, 'category' => $categorySlug]),
            UserLibraryKind::Game  => route('games.parentalguide.category', ['game' => $this->parent, 'category' => $categorySlug]),
        };
    }

    /**
     * Returns the URL of the active kind's parental guide overview page.
     *
     * @return string
     */
    public function getParentalGuideUrlProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => route('anime.parentalguide', $this->parent),
            UserLibraryKind::Manga => route('manga.parentalguide', $this->parent),
            UserLibraryKind::Game  => route('games.parentalguide', $this->parent),
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.parental-guide-category-entries');
    }

    /**
     * @inheritDoc
     */
    protected function listingTargetModel(): Model
    {
        return $this->parent;
    }
}
