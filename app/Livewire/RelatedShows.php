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

class RelatedShows extends Component
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
        $this->parent->loadMissing(['media']);
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
     * The list of related anime for the parent model.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getAnimeRelationsProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->parent->animeRelations()
            ->with([
                'related' => function ($query) {
                    $query->with(['genres', 'media', 'mediaStat', 'themes', 'translation', 'tv_rating'])
                        ->when(auth()->user(), function ($query, $user) {
                            $query->with(['library' => function ($query) use ($user) {
                                $query->where('user_id', '=', $user->id);
                            }]);
                        });
                },
                'relation',
            ])
            ->paginate(25);
    }

    /**
     * Returns the leading page-title fragment.
     *
     * @return string
     */
    public function getPageTitleProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime, UserLibraryKind::Game => __('Relations'),
            UserLibraryKind::Manga => __('Adaptations'),
        };
    }

    /**
     * Returns the page description used in og:description and meta description.
     *
     * @return string
     */
    public function getPageDescriptionProperty(): string
    {
        return __('An extensive list of sequel, prequel, side story, spin off, and adaptations of :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $this->parent->title, 'y' => config('app.name')]);
    }

    /**
     * Returns the heading text shown above the list.
     *
     * @return string
     */
    public function getH1Property(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime, UserLibraryKind::Game => __(':x’s Related Anime', ['x' => $this->parent->title]),
            UserLibraryKind::Manga => __(':x’s Adaptations', ['x' => $this->parent->title]),
        };
    }

    /**
     * Returns the og:type meta value.
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
     * Returns the canonical URL for the active kind's related-anime page.
     *
     * @return string
     */
    public function getCanonicalUrlProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => route('anime.related-anime', $this->parent),
            UserLibraryKind::Manga => route('manga.related-anime', $this->parent),
            UserLibraryKind::Game  => route('games.related-anime', $this->parent),
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.related-shows');
    }
}
