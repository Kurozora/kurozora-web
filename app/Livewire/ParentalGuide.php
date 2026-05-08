<?php

namespace App\Livewire;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\ParentalGuideEntry;
use App\Traits\Livewire\ParentalGuideEntryActions;
use App\Traits\Livewire\ParentalGuideSubmission;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Component;

class ParentalGuide extends Component
{
    use ParentalGuideEntryActions;
    use ParentalGuideSubmission;

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
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

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

        $authUser = auth()->user();

        $this->parent->loadMissing([
            'media',
            'translation',
            'parental_guide_entries' => function ($query) use ($authUser) {
                $query->visible()
                    ->withReason()
                    ->with(ParentalGuideEntry::lockupEagerLoads($authUser));
            },
            'parental_guide_stat',
        ]);
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
     * Sets the property to load the page.
     *
     * @return void
     */
    public function loadPage(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Get the entries grouped by category.
     *
     * @return Collection
     */
    public function getParentalGuideEntriesProperty(): Collection
    {
        $authUser = auth()->user();
        $parent = $this->parent;

        return ParentalGuideEntry::query()
            ->visible()
            ->withReason()
            ->where('model_type', '=', $parent->getMorphClass())
            ->where('model_id', '=', $parent->getKey())
            ->with(ParentalGuideEntry::lockupEagerLoads($authUser))
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('category');
    }

    /**
     * Map each category to the total number of (visible, non-empty) entries it has.
     *
     * @return Collection
     */
    public function getCategoryEntryCountsProperty(): Collection
    {
        return $this->parentalGuideEntries->map->count();
    }

    /**
     * Returns the og:type meta value for the active kind.
     *
     * @return string
     */
    public function getOgTypeProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime, UserLibraryKind::Game => 'video.tv_show',
            UserLibraryKind::Manga => 'book',
        };
    }

    /**
     * Returns the og:image fallback poster filename for the active kind.
     *
     * @return string
     */
    public function getOgImagePosterProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime, UserLibraryKind::Manga => 'anime_poster.webp',
            UserLibraryKind::Game  => 'game_poster.webp',
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
     * Returns the canonical URL for the active kind's parental guide page.
     *
     * @return string
     */
    public function getCanonicalUrlProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => route('anime.parentalguide', $this->parent),
            UserLibraryKind::Manga => route('manga.parentalguide', $this->parent),
            UserLibraryKind::Game  => route('games.parentalguide', $this->parent),
        };
    }

    /**
     * Returns the route URL of a given parental guide category for the active kind.
     *
     * @param string $categorySlug
     *
     * @return string
     */
    public function categoryRoute(string $categorySlug): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => route('anime.parentalguide.category', ['anime' => $this->parent, 'category' => $categorySlug]),
            UserLibraryKind::Manga => route('manga.parentalguide.category', ['manga' => $this->parent, 'category' => $categorySlug]),
            UserLibraryKind::Game  => route('games.parentalguide.category', ['game' => $this->parent, 'category' => $categorySlug]),
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.parental-guide');
    }

    /**
     * @inheritDoc
     */
    protected function submissionTargetModel(): Model
    {
        return $this->parent;
    }

    /**
     * @inheritDoc
     */
    protected function afterSubmit(): void
    {
        $authUser = auth()->user();

        $this->parent->load([
            'parental_guide_entries' => function ($query) use ($authUser) {
                $query->visible()->withReason()->with(ParentalGuideEntry::lockupEagerLoads($authUser));
            },
            'parental_guide_stat',
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function afterEntryDeleted(): void
    {
        $this->afterSubmit();
    }
}
