<?php

namespace App\Http\Controllers\Web;

use App\Enums\MediaCollection;
use App\Enums\UserLibraryKind;
use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Game;
use App\Models\KModel;
use App\Models\Manga;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MuseumController extends Controller
{
    /**
     * Show the museum of the given kind.
     *
     * @param int $kind
     *
     * @return Application|Factory|View
     */
    public function index(int $kind): Application|Factory|View
    {
        $years = $this->years($kind);
        $counts = array_column($years, 'count');
        $yearNumbers = array_column($years, 'year');
        $isGame = $kind === UserLibraryKind::Game;

        return view('museum.index', [
            'slug' => $this->slug($kind),
            'kind' => $kind,
            'years' => $years,
            'currentYear' => $this->currentYear($yearNumbers),
            'itemHeight' => $isGame ? 112 : 160,
            'itemHeightRem' => $isGame ? '7rem' : '10rem',
            'skeletonClass' => $isGame ? 'h-28 w-28 rounded-3xl' : 'h-40 w-28 rounded-lg',
            'decades' => collect($years)->groupBy(fn ($year) => intdiv($year['year'], 10) * 10),
            'maxCount' => empty($counts) ? 1 : max($counts),
            'totalCount' => array_sum($counts),
            'startYear' => empty($yearNumbers) ? null : min($yearNumbers),
            'endYear' => empty($yearNumbers) ? null : max($yearNumbers),
            'canonicalUrl' => $this->canonicalUrl($kind),
            'ogDescription' => $this->ogDescription($kind),
        ]);
    }

    /**
     * Return the posters for every entry released in the given year.
     *
     * @param Request $request
     * @param int     $year
     *
     * @return JsonResponse
     */
    public function byYear(Request $request, int $year): JsonResponse
    {
        $kind = (int) $request->route('kind');
        $dateColumn = $this->dateColumn($kind);
        $user = auth()->user();

        $entries = $this->modelClass($kind)::withoutIgnoreList()
            ->whereBetween($dateColumn, [$year . '-01-01', $year . '-12-31'])
            ->with([
                'translation',
                'media' => fn ($query) => $query->where('collection_name', '=', MediaCollection::Poster),
            ])
            ->when($user, fn ($query) => $query->withCount([
                'trackers as in_library' => fn ($trackers) => $trackers->whereKey($user->getKey()),
            ]))
            ->orderBy($dateColumn)
            ->orderBy('id')
            ->get();

        return response()->json(
            $entries->map(fn (KModel $entry) => [
                'title' => $entry->title,
                'url' => $this->detailsUrl($kind, $entry),
                'poster' => $entry->getFirstMediaFullUrl(MediaCollection::Poster()) ?? asset('images/static/placeholders/anime_poster.webp'),
                'backgroundColor' => $entry->getFirstMedia(MediaCollection::Poster)?->custom_properties['background_color'] ?? '#244630',
                'inLibrary' => (bool) ($entry->in_library ?? false),
            ])
        );
    }

    /**
     * The release years of the given kind that hold entries paired with their counts.
     *
     * @param int $kind
     *
     * @return array
     */
    private function years(int $kind): array
    {
        $dateColumn = $this->dateColumn($kind);

        return $this->modelClass($kind)::withoutIgnoreList()
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
    }

    /**
     * The year the museum opens on.
     *
     * @param array $availableYears
     *
     * @return int
     */
    private function currentYear(array $availableYears): int
    {
        $currentYear = now()->year;

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
     * The model class backing the given kind.
     *
     * @param int $kind
     *
     * @return class-string
     */
    private function modelClass(int $kind): string
    {
        return match ($kind) {
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class,
            default => Anime::class,
        };
    }

    /**
     * The release date column of the given kind.
     *
     * @param int $kind
     *
     * @return string
     */
    private function dateColumn(int $kind): string
    {
        return $kind === UserLibraryKind::Game ? 'published_at' : 'started_at';
    }

    /**
     * The URL slug of the given kind.
     *
     * @param int $kind
     *
     * @return string
     */
    private function slug(int $kind): string
    {
        return match ($kind) {
            UserLibraryKind::Manga => 'manga',
            UserLibraryKind::Game => 'games',
            default => 'anime',
        };
    }

    /**
     * The canonical URL of the museum of the given kind.
     *
     * @param int $kind
     *
     * @return string
     */
    private function canonicalUrl(int $kind): string
    {
        return match ($kind) {
            UserLibraryKind::Manga => route('museum.manga'),
            UserLibraryKind::Game => route('museum.games'),
            default => route('museum.anime'),
        };
    }

    /**
     * The OG description of the museum of the given kind.
     *
     * @param int $kind
     *
     * @return string
     */
    private function ogDescription(int $kind): string
    {
        return match ($kind) {
            UserLibraryKind::Manga => __('Walk through every manga year by year on :x, from the earliest releases to the newest. Explore the full timeline of anime, manga and games on the largest, free online database!', ['x' => config('app.name')]),
            UserLibraryKind::Game => __('Walk through every game year by year on :x, from the earliest releases to the newest. Explore the full timeline of anime, manga and games on the largest, free online database!', ['x' => config('app.name')]),
            default => __('Walk through every anime year by year on :x, from the earliest releases to the newest. Explore the full timeline of anime, manga and games on the largest, free online database!', ['x' => config('app.name')]),
        };
    }

    /**
     * The web details URL for the given entry.
     *
     * @param int    $kind
     * @param KModel $entry
     *
     * @return string
     */
    private function detailsUrl(int $kind, KModel $entry): string
    {
        return match ($kind) {
            UserLibraryKind::Manga => route('manga.details', $entry->slug),
            UserLibraryKind::Game => route('games.details', $entry->slug),
            default => route('anime.details', $entry->slug),
        };
    }
}
