<?php

namespace App\Livewire\Museum;

use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    /**
     * The library kind being walked through.
     *
     * @var int $kind
     */
    public int $kind = UserLibraryKind::Anime;

    /**
     * The release years that hold entries, each paired with its count.
     *
     * @var array $years
     */
    public array $years = [];

    /**
     * The year the museum scrolls to on load.
     *
     * @var int $currentYear
     */
    public int $currentYear;

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
        $dateColumn = $this->dateColumn();

        $this->years = $this->modelClass()::withoutIgnoreList()
            ->whereNotNull($dateColumn)
            ->selectRaw('YEAR(' . $dateColumn . ') as year, COUNT(*) as count')
            ->groupByRaw('YEAR(' . $dateColumn . ')')
            ->orderByRaw('YEAR(' . $dateColumn . ')')
            ->get()
            ->map(fn ($row) => [
                'year' => (int) $row->year,
                'count' => (int) $row->count,
            ])
            ->all();

        $this->currentYear = $this->resolveCurrentYear();
    }

    /**
     * Resolve the year to open on: the current year, else the most recent past year.
     *
     * @return int
     */
    private function resolveCurrentYear(): int
    {
        $currentYear = now()->year;
        $availableYears = array_column($this->years, 'year');

        if (in_array($currentYear, $availableYears, true)) {
            return $currentYear;
        }

        $pastYears = array_filter($availableYears, fn ($year) => $year <= $currentYear);

        if (!empty($pastYears)) {
            return max($pastYears);
        }

        return empty($availableYears) ? $currentYear : min($availableYears);
    }

    /**
     * The model class backing the current kind.
     *
     * @return class-string
     */
    private function modelClass(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
            default => Anime::class,
        };
    }

    /**
     * The release date column for the current kind.
     *
     * @return string
     */
    private function dateColumn(): string
    {
        return $this->kind === UserLibraryKind::Game ? 'published_at' : 'started_at';
    }

    /**
     * The URL slug for the current kind.
     *
     * @return string
     */
    private function slug(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Manga => 'manga',
            UserLibraryKind::Game => 'games',
            default => 'anime',
        };
    }

    /**
     * The canonical URL of the current museum.
     *
     * @return string
     */
    public function getCanonicalUrlProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Manga => route('museum.manga'),
            UserLibraryKind::Game => route('museum.games'),
            default => route('museum.anime'),
        };
    }

    /**
     * The OG description for the current kind.
     *
     * @return string
     */
    public function getOgDescriptionProperty(): string
    {
        return match ($this->kind) {
            UserLibraryKind::Manga => __('Walk through every manga year by year on :x, from the earliest releases to the newest. Explore the full timeline of anime, manga and games on the largest, free online database!', ['x' => config('app.name')]),
            UserLibraryKind::Game => __('Walk through every game year by year on :x, from the earliest releases to the newest. Explore the full timeline of anime, manga and games on the largest, free online database!', ['x' => config('app.name')]),
            default => __('Walk through every anime year by year on :x, from the earliest releases to the newest. Explore the full timeline of anime, manga and games on the largest, free online database!', ['x' => config('app.name')]),
        };
    }

    /**
     * Render the component.
     *
     * @return Application|Factory|View
     */
    public function render(): Application|Factory|View
    {
        $counts = array_column($this->years, 'count');
        $yearNumbers = array_column($this->years, 'year');
        $isGame = $this->kind === UserLibraryKind::Game;

        return view('livewire.museum.index', [
            'slug' => $this->slug(),
            'kind' => $this->kind,
            'itemHeight' => $isGame ? 112 : 160,
            'itemHeightRem' => $isGame ? '7rem' : '10rem',
            'skeletonClass' => $isGame ? 'h-28 w-28 rounded-3xl' : 'h-40 w-28 rounded-lg',
            'decades' => collect($this->years)->groupBy(fn ($year) => intdiv($year['year'], 10) * 10),
            'maxCount' => empty($counts) ? 1 : max($counts),
            'totalCount' => array_sum($counts),
            'startYear' => empty($yearNumbers) ? null : min($yearNumbers),
            'endYear' => empty($yearNumbers) ? null : max($yearNumbers),
        ]);
    }
}
