<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\TrailerSort;
use App\Enums\UserLibraryKind;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetTrailersRequest;
use App\Http\Resources\VideoResource;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\JsonResponse;

class TrailerController extends Controller
{
    /**
     * The number of days a video counts as a recent release.
     */
    private const int TRENDING_DAYS = 30;

    /**
     * Retrieves the trailers of the given kind.
     *
     * @param GetTrailersRequest $request
     *
     * @return JsonResponse
     */
    public function index(GetTrailersRequest $request): JsonResponse
    {
        $data = $request->validated();
        $kind = (int) $request->route('kind');
        $sort = (int) ($data['sort'] ?? TrailerSort::JustAdded);
        $modelClass = $this->modelClass($kind);

        $videos = Video::whereHasMorph('videoable', [$modelClass], function (EloquentBuilder $query) use ($kind, $modelClass, $sort) {
            if ($sort === TrailerSort::MostAnticipated) {
                $query->whereDate($modelClass::TABLE_NAME . '.' . $this->releaseDateColumn($kind), '>', today());
            }
        })
            ->whereIn(Video::TABLE_NAME . '.id', $this->representativeVideoIds($modelClass))
            ->with('videoable');

        if ($sort === TrailerSort::Trending) {
            $videos->whereBetween(Video::TABLE_NAME . '.published_at', [today()->subDays(self::TRENDING_DAYS), now()]);
        }

        match ($sort) {
            TrailerSort::JustAdded => $videos->orderByDesc(Video::TABLE_NAME . '.published_at')
                ->orderByDesc(Video::TABLE_NAME . '.id'),
            default => $videos->orderByDesc(Video::TABLE_NAME . '.view_count')
                ->orderByDesc(Video::TABLE_NAME . '.id'),
        };

        $videos = $videos->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $videos->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => VideoResource::collection($videos),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * The ids of the one video that stands in for each title.
     *
     * @param string $modelClass
     *
     * @return EloquentBuilder
     */
    private function representativeVideoIds(string $modelClass): EloquentBuilder
    {
        $ranked = Video::selectRaw('id, ROW_NUMBER() OVER (PARTITION BY videoable_id ORDER BY published_at DESC, id DESC) AS ranking')
            ->where('videoable_type', '=', $modelClass);

        return Video::withoutGlobalScopes()
            ->select('id')
            ->fromSub($ranked, 'ranked')
            ->where('ranking', '=', 1);
    }

    /**
     * Returns the release date column of the given kind.
     *
     * @param int $kind
     *
     * @return string
     */
    private function releaseDateColumn(int $kind): string
    {
        return $kind === UserLibraryKind::Game ? 'published_at' : 'started_at';
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
            UserLibraryKind::Game => Game::class,
            default => Anime::class,
        };
    }
}
