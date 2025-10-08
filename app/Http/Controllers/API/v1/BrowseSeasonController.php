<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\BrowseSeasonKind;
use App\Enums\SeasonOfYear;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetBrowseSeasonRequest;
use App\Http\Resources\AnimeResource;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;

class BrowseSeasonController extends Controller
{
    /**
     * Returns detailed information of an Anime.
     *
     * @param GetBrowseSeasonRequest $request
     * @param int                    $year
     * @param string                 $season
     *
     * @return JsonResponse
     * @throws InvalidEnumKeyException
     * @throws ConnectionException
     */
    public function view(GetBrowseSeasonRequest $request, int $year, string $season): JsonResponse
    {
        $data = $request->validated();
        $browseSectionKind = BrowseSeasonKind::fromValue((int) $data['kind']);
        $mediaTypes = $data['mediaTypes'] ?? [];

        $season = SeasonOfYear::fromKey(str($season)->ucfirst());
        $model = match ($browseSectionKind->value) {
            BrowseSeasonKind::Game => Game::class,
            BrowseSeasonKind::Manga => Manga::class,
            default => Anime::class,
        };
        $seasonOfYearKey = match ($browseSectionKind->value) {
            BrowseSeasonKind::Game,
            BrowseSeasonKind::Manga => 'publication_season',
            default => 'air_season'
        };
        $startedAtKey = match ($browseSectionKind->value) {
            BrowseSeasonKind::Game => 'published_at',
            default => 'started_at'
        };
        $dayKey = match ($browseSectionKind->value) {
            BrowseSeasonKind::Game,
            BrowseSeasonKind::Manga  => 'publication_day',
            default => 'air_day'
        };

        $anime = $model::with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
            ->where([
                [$seasonOfYearKey, '=', $season],
                [$startedAtKey, '>=', $year . '-01-01'],
                [$startedAtKey, '<=', $year . '-12-31'],
            ])
            ->when($mediaTypes !== [], function ($query) use ($mediaTypes) {
                $query->whereIn('media_type_id', $mediaTypes);
            })
            ->orderBy($dayKey)
            ->get();

        // Show the Anime details response
        return JSONResult::success([
            'data' => AnimeResource::collection($anime)
        ]);
    }
}
