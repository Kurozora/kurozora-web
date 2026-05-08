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
class RelationsSection extends Component
{
    /**
     * The library kind being viewed.
     *
     * @var int $kind
     */
    public int $kind = UserLibraryKind::Anime;

    /**
     * The cross-kind whose relations should be surfaced.
     *
     * @var int $relatedKind
     */
    public int $relatedKind = UserLibraryKind::Anime;

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
     * Whether the component is ready to load.
     *
     * @var bool $readyToLoad
     */
    public bool $readyToLoad = false;

    /**
     * Prepare the component.
     *
     * @param int $kind
     * @param int $relatedKind
     *
     * @return void
     */
    public function mount(int $kind, int $relatedKind): void
    {
        $this->kind = $kind;
        $this->relatedKind = $relatedKind;

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
     * Sets the property to load the section.
     *
     * @return void
     */
    public function loadSection(): void
    {
        $this->readyToLoad = true;
    }

    /**
     * Loads the cross-kind relations for the active parent.
     *
     * @return Collection
     */
    public function getRelationsProperty(): Collection
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        $relation = match ($this->relatedKind) {
            UserLibraryKind::Anime => $this->parent->animeRelations(),
            UserLibraryKind::Manga => $this->parent->mangaRelations(),
            UserLibraryKind::Game  => $this->parent->gameRelations(),
        };

        return $relation
            ->with([
                'related' => function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
                        ->when(auth()->user(), function ($query, $user) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        });
                },
                'relation'
            ])
            ->limit($this->maximumLimit())
            ->get();
    }

    /**
     * Returns the section title for the active cell.
     *
     * @return string
     */
    public function getSectionTitleProperty(): string
    {
        return match ([$this->kind, $this->relatedKind]) {
            [UserLibraryKind::Anime, UserLibraryKind::Anime] => __('Related'),
            [UserLibraryKind::Anime, UserLibraryKind::Manga] => __('Adaptations'),
            [UserLibraryKind::Anime, UserLibraryKind::Game]  => __('Games'),
            [UserLibraryKind::Manga, UserLibraryKind::Manga] => __('Related'),
            [UserLibraryKind::Manga, UserLibraryKind::Anime] => __('Adaptations'),
            [UserLibraryKind::Manga, UserLibraryKind::Game]  => __('Games'),
            [UserLibraryKind::Game,  UserLibraryKind::Game]  => __('Related'),
            [UserLibraryKind::Game,  UserLibraryKind::Anime] => __('Shows'),
            [UserLibraryKind::Game,  UserLibraryKind::Manga] => __('Literatures'),
        };
    }

    /**
     * Returns the route used by the "See All" link for the active cell.
     *
     * @return string
     */
    public function getSeeAllUrlProperty(): string
    {
        return match ([$this->kind, $this->relatedKind]) {
            [UserLibraryKind::Anime, UserLibraryKind::Anime] => route('anime.related-anime', $this->parent),
            [UserLibraryKind::Anime, UserLibraryKind::Manga] => route('anime.related-mangas', $this->parent),
            [UserLibraryKind::Anime, UserLibraryKind::Game]  => route('anime.related-games', $this->parent),
            [UserLibraryKind::Manga, UserLibraryKind::Manga] => route('manga.related-mangas', $this->parent),
            [UserLibraryKind::Manga, UserLibraryKind::Anime] => route('manga.related-anime', $this->parent),
            [UserLibraryKind::Manga, UserLibraryKind::Game]  => route('manga.related-games', $this->parent),
            [UserLibraryKind::Game,  UserLibraryKind::Game]  => route('games.related-games', $this->parent),
            [UserLibraryKind::Game,  UserLibraryKind::Anime] => route('games.related-anime', $this->parent),
            [UserLibraryKind::Game,  UserLibraryKind::Manga] => route('games.related-literatures', $this->parent),
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
        return view('livewire.components.relations-section');
    }
}
