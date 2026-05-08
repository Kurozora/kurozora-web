<?php

namespace App\Livewire\Browse\Seasons;

use App\Enums\SeasonOfYear;
use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaType;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Index extends Component
{
    /**
     * The library kind being viewed.
     *
     * @var int $kind
     */
    public int $kind = UserLibraryKind::Anime;

    /**
     * The selected year.
     *
     * @var int|null $year
     */
    public $year;

    /**
     * The selected season.
     *
     * @var string $season
     */
    public string $season;

    /**
     * Prepare the component.
     *
     * @param int    $kind
     * @param string $year
     * @param string $season
     *
     * @return void
     */
    public function mount(int $kind, string $year, string $season): void
    {
        $this->kind = $kind;

        $year = (int) $year;

        if ($year < 1917) {
            $this->redirectToSeasonsIndex();
            return;
        }

        if (!is_numeric($year)) {
            $this->redirectToSeasonsIndex();
            return;
        }

        try {
            $this->season = SeasonOfYear::fromKey(str($season)->ucfirst())->key;
        } catch (InvalidEnumKeyException $e) {
            $this->redirectToSeasonsIndex();
            return;
        }
        $this->year = $year;
    }

    /**
     * Redirects to the seasons-index landing page for the active kind.
     *
     * @return void
     */
    protected function redirectToSeasonsIndex(): void
    {
        match ($this->kind) {
            UserLibraryKind::Anime => $this->redirectRoute('anime.seasons.index'),
            UserLibraryKind::Manga => $this->redirectRoute('manga.seasons.index'),
            UserLibraryKind::Game  => $this->redirectRoute('games.seasons.index'),
        };
    }

    /**
     * Get the SeasonOfYear object.
     *
     * @return SeasonOfYear
     * @throws InvalidEnumKeyException
     */
    public function getSeasonOfYearProperty(): SeasonOfYear
    {
        return SeasonOfYear::fromKey(str($this->season)->ucfirst());
    }

    /**
     * Get the available Media Types.
     *
     * @return Collection<int, MediaType>
     */
    public function getMediaTypesProperty(): Collection
    {
        $modelClass = $this->modelClass();
        $seasonColumn = $this->seasonColumn();
        $dateColumn = $this->dateColumn();

        return MediaType::select(MediaType::TABLE_NAME . '.*')
            ->join($modelClass::TABLE_NAME, function ($join) use ($modelClass, $seasonColumn, $dateColumn) {
                $join->on($modelClass::TABLE_NAME . '.media_type_id', '=', MediaType::TABLE_NAME . '.id')
                    ->where([
                        [$seasonColumn, '=', $this->seasonOfYear->value],
                        [$dateColumn, '>=', $this->year . '-01-01'],
                        [$dateColumn, '<=', $this->year . '-12-31'],
                    ]);
            })
            ->groupBy('id', 'name', 'description')
            ->get();
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
     * Returns the season column on the active model.
     *
     * @return string
     */
    protected function seasonColumn(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => 'air_season',
            UserLibraryKind::Manga, UserLibraryKind::Game => 'publication_season',
        };
    }

    /**
     * Returns the date column on the active model.
     *
     * @return string
     */
    protected function dateColumn(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime, UserLibraryKind::Manga => 'started_at',
            UserLibraryKind::Game => 'published_at',
        };
    }

    /**
     * Returns the canonical URL for the active kind's year+season grid.
     *
     * @return string
     */
    public function getCanonicalUrlProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => route('anime.seasons.year.season', [$this->year, $this->season]),
            UserLibraryKind::Manga => route('manga.seasons.year.season', [$this->year, $this->season]),
            UserLibraryKind::Game  => route('games.seasons.year.season', [$this->year, $this->season]),
        };
    }

    /**
     * Returns the localized noun used in og:title and document title.
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
     * Returns the og:description and meta description for the active kind.
     *
     * @return string
     */
    public function getOgDescriptionProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('Browse the :x :y anime season. Join the :z community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $this->seasonOfYear->key, 'y' => $this->year, 'z' => config('app.name')]),
            UserLibraryKind::Manga => __('Browse the :x :y manga season. Join the :z community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $this->seasonOfYear->key, 'y' => $this->year, 'z' => config('app.name')]),
            UserLibraryKind::Game  => __('Browse the :x :y game season. Join the :z community and create your anime, manga and game list. Discover songs, episodes and read reviews and news!', ['x' => $this->seasonOfYear->key, 'y' => $this->year, 'z' => config('app.name')]),
        };
    }

    /**
     * Returns the heading shown above the season grid.
     *
     * @return string
     */
    public function getHeadingProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('Seasonal Anime'),
            UserLibraryKind::Manga => __('Seasonal Manga'),
            UserLibraryKind::Game  => __('Seasonal Games'),
        };
    }

    /**
     * Returns the empty-state placeholder image filename.
     *
     * @return string
     */
    public function getOgImagePosterProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => 'empty_anime_library.webp',
            UserLibraryKind::Manga => 'empty_manga_library.webp',
            UserLibraryKind::Game  => 'empty_game_library.webp',
        };
    }

    /**
     * Returns the empty-state heading.
     *
     * @return string
     */
    public function getEmptyHeadingProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('No Anime'),
            UserLibraryKind::Manga => __('No Manga'),
            UserLibraryKind::Game  => __('No Games'),
        };
    }

    /**
     * Returns the empty-state body copy.
     *
     * @return string
     */
    public function getEmptyDescriptionProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Anime => __('There are no anime airing this season.'),
            UserLibraryKind::Manga => __('There are no manga publishing this season.'),
            UserLibraryKind::Game  => __('There are no games publishing this season.'),
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        return view('livewire.browse.seasons.index');
    }
}
