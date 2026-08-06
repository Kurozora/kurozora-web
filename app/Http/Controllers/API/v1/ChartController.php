<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\ChartKind;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetPaginatedRequest;
use App\Http\Resources\AnimeResourceIdentity;
use App\Http\Resources\CharacterResourceIdentity;
use App\Http\Resources\EpisodeResourceIdentity;
use App\Http\Resources\GameResourceIdentity;
use App\Http\Resources\LiteratureResourceIdentity;
use App\Http\Resources\PersonResourceIdentity;
use App\Http\Resources\SongResourceIdentity;
use App\Http\Resources\StudioResourceIdentity;
use App\Models\Anime;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Person;
use App\Models\Song;
use App\Models\Studio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChartController extends Controller
{
    /**
     * Retrieves the top-ranked entries for the given chart kind.
     *
     * @param GetPaginatedRequest $request
     * @param string              $chart
     *
     * @return JsonResponse
     */
    public function view(GetPaginatedRequest $request, string $chart): JsonResponse
    {
        $data = $request->validated();

        $entries = $this->modelClass($chart)::where('rank_total', '!=', 0)
            ->orderBy('rank_total')
            ->orderBy('id')
            ->cursorPaginate($data['limit'] ?? 25);

        // Get next page url minus domain
        $nextPageURL = str_replace($request->root(), '', $entries->nextPageUrl() ?? '');

        return JSONResult::success([
            'data' => $this->identityCollection($chart, $entries),
            'next' => empty($nextPageURL) ? null : $nextPageURL,
        ]);
    }

    /**
     * Returns the model class for the given chart kind.
     *
     * @param string $chart
     *
     * @return class-string
     */
    private function modelClass(string $chart): string
    {
        return match ($chart) {
            ChartKind::Anime => Anime::class,
            ChartKind::Characters => Character::class,
            ChartKind::Episodes => Episode::class,
            ChartKind::Games => Game::class,
            ChartKind::Manga => Manga::class,
            ChartKind::People => Person::class,
            ChartKind::Songs => Song::class,
            ChartKind::Studios => Studio::class,
        };
    }

    /**
     * Wraps the given entries in the identity resource for the given chart kind.
     *
     * @param string $chart
     * @param mixed  $entries
     *
     * @return AnonymousResourceCollection
     */
    private function identityCollection(string $chart, mixed $entries): AnonymousResourceCollection
    {
        return match ($chart) {
            ChartKind::Anime => AnimeResourceIdentity::collection($entries),
            ChartKind::Characters => CharacterResourceIdentity::collection($entries),
            ChartKind::Episodes => EpisodeResourceIdentity::collection($entries),
            ChartKind::Games => GameResourceIdentity::collection($entries),
            ChartKind::Manga => LiteratureResourceIdentity::collection($entries),
            ChartKind::People => PersonResourceIdentity::collection($entries),
            ChartKind::Songs => SongResourceIdentity::collection($entries),
            ChartKind::Studios => StudioResourceIdentity::collection($entries),
        };
    }
}
