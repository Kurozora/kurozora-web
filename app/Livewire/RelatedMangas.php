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

class RelatedMangas extends Component
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
     * The list of related mangas for the parent model.
     *
     * @return Collection|LengthAwarePaginator
     */
    public function getMangaRelationsProperty(): Collection|LengthAwarePaginator
    {
        if (!$this->readyToLoad) {
            return collect();
        }

        return $this->parent->mangaRelations()
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
            UserLibraryKind::Anime, UserLibraryKind::Game => __('Adaptations'),
            UserLibraryKind::Manga => __('Relations'),
        };
    }

    /**
     * Returns the page description used in og:description and meta description.
     *
     * @return string
     */
    public function getPageDescriptionProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('An extensive list of manga, manhua, manhwa, and light novel adaptations of :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $this->parent->title, 'y' => config('app.name')]),
            UserLibraryKind::Manga => __('An extensive list of sequel, prequel, side story, spin off, and adaptations of :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $this->parent->title, 'y' => config('app.name')]),
            UserLibraryKind::Game  => __('An extensive list of game, mod, and dlc to :x only on :y, the largest, free online anime, manga, game & music database in the world.', ['x' => $this->parent->title, 'y' => config('app.name')]),
        };
    }

    /**
     * Returns the heading text shown above the list.
     *
     * @return string
     */
    public function getH1Property(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime, UserLibraryKind::Game => __(':x’s Adaptations', ['x' => $this->parent->title]),
            UserLibraryKind::Manga => __(':x’s Related Mangas', ['x' => $this->parent->title]),
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
     * Returns the og:image fallback poster filename.
     *
     * @return string
     */
    public function getOgImagePosterProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime, UserLibraryKind::Manga => 'anime_poster.webp',
            UserLibraryKind::Game => 'game_poster.webp',
        };
    }

    /**
     * Returns the URL slug used in the appArgument and canonical paths.
     *
     * Games preserve the `related-literatures` slug for SEO/back-compat.
     *
     * @return string
     */
    public function getRouteSlugProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime, UserLibraryKind::Manga => 'related-mangas',
            UserLibraryKind::Game => 'related-literatures',
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
     * Returns the canonical URL for the active kind's related-mangas page.
     *
     * Games preserve the `games.related-literatures` route name for SEO/back-compat.
     *
     * @return string
     */
    public function getCanonicalUrlProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => route('anime.related-mangas', $this->parent),
            UserLibraryKind::Manga => route('manga.related-mangas', $this->parent),
            UserLibraryKind::Game  => route('games.related-literatures', $this->parent),
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.related-mangas');
    }
}
