<?php

namespace App\Livewire\Browse\Seasons;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Archive extends Component
{
    /**
     * The library kind being viewed.
     *
     * @var int $kind
     */
    public int $kind = UserLibraryKind::Anime;

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
    }

    /**
     * Returns the model class for the active library kind.
     *
     * @return string
     */
    public function modelClass(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => Anime::class,
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game  => Game::class,
        };
    }

    /**
     * Returns the URL for the given year+season cell of the active kind.
     *
     * @param int    $year
     * @param string $seasonOfYear
     *
     * @return string
     */
    public function urlForYearSeason(int $year, string $seasonOfYear): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => route('anime.seasons.year.season', [$year, $seasonOfYear]),
            UserLibraryKind::Manga => route('manga.seasons.year.season', [$year, $seasonOfYear]),
            UserLibraryKind::Game  => route('games.seasons.year.season', [$year, $seasonOfYear]),
        };
    }

    /**
     * Returns the canonical URL for the active kind's seasons archive.
     *
     * @return string
     */
    public function getCanonicalUrlProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => route('anime.seasons.archive'),
            UserLibraryKind::Manga => route('manga.seasons.archive'),
            UserLibraryKind::Game  => route('games.seasons.archive'),
        };
    }

    /**
     * Returns the localized noun used in document and og titles.
     *
     * @return string
     */
    public function getOgTitleNounProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('Anime'),
            UserLibraryKind::Manga => __('Manga'),
            UserLibraryKind::Game  => __('Games'),
        };
    }

    /**
     * Returns the OpenGraph description for the active kind.
     *
     * @return string
     */
    public function getOgDescriptionProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('Browse the archive of anime seasons. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]),
            UserLibraryKind::Manga => __('Browse the archive of manga seasons. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]),
            UserLibraryKind::Game  => __('Browse the archive of game seasons. Join the :x community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => config('app.name')]),
        };
    }

    /**
     * Returns the heading shown above the archive table.
     *
     * @return string
     */
    public function getHeadingProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('Seasonal Anime Archive'),
            UserLibraryKind::Manga => __('Seasonal Manga Archive'),
            UserLibraryKind::Game  => __('Seasonal Games Archive'),
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.browse.seasons.archive');
    }
}
