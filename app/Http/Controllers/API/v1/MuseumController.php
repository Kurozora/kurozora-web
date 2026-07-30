<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\MediaCollection;
use App\Enums\UserLibraryKind;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Resources\AnimeResourcePoster;
use App\Http\Resources\GameResourcePoster;
use App\Http\Resources\LiteratureResourcePoster;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MuseumController extends Controller
{
    /**
     * Retrieves the release years that hold entries.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $kind = (int) $request->route('kind');
        $dateColumn = $this->dateColumn($kind);

        $years = $this->modelClass($kind)::withoutIgnoreList()
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

        return JSONResult::success([
            'data' => $years,
        ]);
    }

    /**
     * Retrieves the entries released in the given year.
     *
     * @param GetPaginatedRequest $request
     * @param int                 $year
     *
     * @return JsonResponse
     */
    public function year(GetPaginatedRequest $request, int $year): JsonResponse
    {
        $data = $request->validated();
        $kind = (int) $request->route('kind');
        $dateColumn = $this->dateColumn($kind);

        $entries = $this->modelClass($kind)::withoutIgnoreList()
            ->whereBetween($dateColumn, [$year . '-01-01', $year . '-12-31'])
            ->with([
                'translation',
                'media' => fn ($query) => $query->where('collection_name', '=', MediaCollection::Poster),
            ])
            ->orderBy($dateColumn)
            ->orderBy('id')
            ->cursorPaginate($data['limit'] ?? 100);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $entries->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => $this->posterCollection($kind, $entries),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Returns the model class for the given kind.
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
     * Returns the release date column for the given kind.
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
     * Wraps the given entries in the poster resource for the given kind.
     *
     * @param int   $kind
     * @param mixed $entries
     *
     * @return AnonymousResourceCollection
     */
    private function posterCollection(int $kind, mixed $entries): AnonymousResourceCollection
    {
        return match ($kind) {
            UserLibraryKind::Manga => LiteratureResourcePoster::collection($entries),
            UserLibraryKind::Game => GameResourcePoster::collection($entries),
            default => AnimeResourcePoster::collection($entries),
        };
    }
}
