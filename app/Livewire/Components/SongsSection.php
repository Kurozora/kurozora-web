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
use Livewire\Component;

#[Isolate]
class SongsSection extends Component
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
     * Manga parent (unused by this widget; declared for shape consistency).
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

        $parent = $this->parent;
        $translation = $parent->translation;

        $stripped = $parent->withoutRelations()
            ->setRelation('translation', $translation);

        match ($this->kind) {
            UserLibraryKind::Anime => $this->anime = $stripped,
            UserLibraryKind::Game  => $this->game = $stripped,
        };
    }

    /**
     * Resolves the active parent model for the current kind.
     *
     * @return Anime|Game
     */
    public function getParentProperty(): Anime|Game
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => $this->anime,
            UserLibraryKind::Game  => $this->game,
        };
    }

    /**
     * Sets the property to load the section.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Loads the media songs section.
     *
     * @return Collection
     */
    public function getMediaSongsProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->parent->mediaSongs()
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
     * Returns the route URL used by the "See All" link for the active kind.
     *
     * @return string
     */
    public function getSeeAllUrlProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => route('anime.songs', $this->parent),
            UserLibraryKind::Game  => route('games.songs', $this->parent),
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
        return view('livewire.components.songs-section');
    }
}
