<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\BrowseSeasonKind;
use App\Enums\SeasonOfYear;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetBrowseSeasonRequest;
use App\Http\Resources\BrowseSeasonResource;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaType;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;

class BrowseSeasonController extends Controller
{
    /**
     * Returns the seasonal browse listing for the given kind, year, and season.
     *
     * @param GetBrowseSeasonRequest $request
     *
     * @return JsonResponse
     * @throws InvalidEnumKeyException
     * @throws ConnectionException
     */
    public function view(GetBrowseSeasonRequest $request): JsonResponse
    {
        $data = $request->validated();
        $browseSectionKind = BrowseSeasonKind::fromValue((int) $data['kind']);
        $year = (int) $data['year'];
        $season = SeasonOfYear::fromValue((int) $data['season']);
        $mediaTypes = $data['mediaTypes'] ?? [];

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

        $items = $model::with(['genres', 'languages', 'media', 'mediaStat', 'mediaType', 'source', 'status', 'studios', 'themes', 'translation', 'tvRating', 'countryOfOrigin'])
            ->when(auth()->user(), function ($query, $user) use ($model) {
                $query->with([
                    'mediaRatings' => fn($q) => $q->where('user_id', $user->id),
                    'library'      => fn($q) => $q->where('user_id', $user->id),
                ])->withExists([
                    'favoriters as isFavorited' => fn($q) => $q->where('user_id', $user->id),
                ]);

                if ($model === Anime::class) {
                    $query->withExists([
                        'reminderers as isReminded' => fn($q) => $q->where('user_id', $user->id),
                    ]);
                }
            })
            ->where([
                [$seasonOfYearKey, '=', $season],
                [$startedAtKey, '>=', $year . '-01-01'],
                [$startedAtKey, '<=', $year . '-12-31'],
            ])
            ->when($mediaTypes !== [], function ($query) use ($mediaTypes) {
                $query->whereIn('media_type_id', $mediaTypes);
            })
            ->orderBy($startedAtKey)
            ->orderBy('id')
            ->limit(200) // This is arbitrary to prevent huge payloads. Adjust as needed.
            ->get();

        $orderedMediaTypeIds = MediaType::select(MediaType::TABLE_NAME . '.id')
            ->join($model::TABLE_NAME, function ($join) use ($model, $seasonOfYearKey, $startedAtKey, $season, $year) {
                $join->on($model::TABLE_NAME . '.media_type_id', '=', MediaType::TABLE_NAME . '.id')
                    ->where([
                        [$seasonOfYearKey, '=', $season],
                        [$startedAtKey, '>=', $year . '-01-01'],
                        [$startedAtKey, '<=', $year . '-12-31'],
                    ]);
            })
            ->when($mediaTypes !== [], function ($query) use ($mediaTypes) {
                $query->whereIn(MediaType::TABLE_NAME . '.id', $mediaTypes);
            })
            ->groupBy(MediaType::TABLE_NAME . '.id', MediaType::TABLE_NAME . '.name', MediaType::TABLE_NAME . '.description')
            ->pluck(MediaType::TABLE_NAME . '.id')
            ->toArray();

        // Group items by media type and order sections by the website's MediaType list.
        $browseSeason = $items
            ->groupBy('media_type_id')
            ->sortBy(function ($models, $mediaTypeId) use ($orderedMediaTypeIds) {
                $position = array_search($mediaTypeId, $orderedMediaTypeIds, true);
                return $position === false ? PHP_INT_MAX : $position;
            })
            ->map(fn($models) => [
                'mediaType' => $models->first()->mediaType,
                'type' => $model,
                'models' => $models,
            ])
            ->values()
            ->toArray();

        return JSONResult::success([
            'data' => BrowseSeasonResource::collection($browseSeason),
        ]);
    }
}
