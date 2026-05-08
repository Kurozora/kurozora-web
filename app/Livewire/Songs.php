<?php

namespace App\Livewire;

use App\Enums\SongType;
use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Songs extends Component
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
     * Get the list of media songs grouped by type description.
     *
     * @return Collection
     */
    public function getMediaSongsProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        $sort = SongType::asSelectArray();

        return $this->parent->mediaSongs()
            ->with([
                'song' => function ($query) {
                    $query->with(['media']);
                },
            ])
            ->get()
            ->sortBy(['position'])
            ->groupBy('type.description')
            ->sortKeysUsing(function ($key1, $key2) use ($sort) {
                $key1 = array_search($key1, $sort);
                $key2 = array_search($key2, $sort);

                return $key1 < $key2 ? -1 : 1;
            });
    }

    /**
     * Returns the canonical URL for the active kind's songs page.
     *
     * @return string
     */
    public function getCanonicalUrlProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => route('anime.songs', $this->parent),
            UserLibraryKind::Game  => route('games.songs', $this->parent),
        };
    }

    /**
     * Returns the canonical URL pointing at the parent's details page.
     *
     * @return string
     */
    public function getDetailsUrlProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => route('anime.details', $this->parent),
            UserLibraryKind::Game  => route('games.details', $this->parent),
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
            UserLibraryKind::Game  => 'games',
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.songs');
    }
}
