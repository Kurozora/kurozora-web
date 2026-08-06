<?php

namespace App\Http\Controllers\Web;

use App\Enums\MediaCollection;
use App\Enums\UserLibraryKind;
use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Game;
use App\Models\KModel;
use App\Models\Manga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MuseumController extends Controller
{
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
        $dateColumn = $kind === UserLibraryKind::Game ? 'published_at' : 'started_at';
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
