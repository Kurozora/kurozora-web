<?php

namespace App\Livewire;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Cast extends Component
{
    use WithPagination;

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
        $this->parent->loadMissing(['media', 'translation']);
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
     * Get the list of cast.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getCastProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

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

        return $this->parent->cast()
            ->with($eagerLoads)
            ->paginate(25);
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
     * Returns the canonical URL for the active kind's cast page.
     *
     * @return string
     */
    public function getCanonicalUrlProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => route('anime.cast', $this->parent),
            UserLibraryKind::Manga => route('manga.cast', $this->parent),
            UserLibraryKind::Game  => route('games.cast', $this->parent),
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.cast');
    }
}
