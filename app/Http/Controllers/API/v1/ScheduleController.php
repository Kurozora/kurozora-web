<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\ScheduleKind;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\Season;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class ScheduleController extends Controller
{
    /**
     * Returns detailed Recap information.
     *
     * @param GetScheduleRequest $request
     *
     * @return JsonResponse
     */
    public function view(GetScheduleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $dates = $this->getDates($data['date'] ?? today()->toDateString());
        $dateRanges = $this->getDateRanges($dates);

        $libraryKind = ScheduleKind::fromValue((int) $data['type']);
        $model = match ($libraryKind->value) {
            ScheduleKind::Game => Game::class,
            ScheduleKind::Manga => Manga::class,
            default => Anime::class
        };

        $schedules = $this->fetchSchedules($model, $dateRanges);

        return JSONResult::success([
            'data' => ScheduleResource::collection($schedules),
        ]);
    }

    private function getDates($date): array
    {
        $date = Carbon::createFromFormat('Y-m-d', $date) ?? now();

        $dates = collect()
            ->push($date->copy()->subDay()) // Previous day
            ->push($date); // Current day
        $next5Days = collect(range(1, 5)) // Next 5 days
        ->map(fn($offset) => $date->copy()->addDays($offset));

        return $dates->merge($next5Days)->toArray();
    }

    private function getDateRanges(array $dates): array
    {
        return array_map(fn($date) => [
            'date' => $date->toDateString(),
            'start' => $date->startOfDay()->toDateTimeString(),
            'end' => $date->endOfDay()->toDateTimeString(),
            'dayOfWeek' => $date->dayOfWeek,
        ], $dates);
    }

    private function fetchSchedules(string $model, array $dateRanges): array
    {
        $query = match ($model) {
            Anime::class => $this->queryAnimeSchedules($dateRanges),
            Manga::class => $this->queryMangaSchedules($dateRanges),
            Game::class => $this->queryGameSchedules($dateRanges),
            default => throw new InvalidArgumentException('Unsupported model type'),
        };

        return $query->get()
            ->groupBy('grouping_date')
            ->map(fn($models, $groupingDate) => [
                'date' => $groupingDate,
                'type' => $model,
                'models' => $models,
            ])
            ->values()
            ->toArray();
    }

    private function queryAnimeSchedules(array $dateRanges)
    {
        $user = auth()->user();

        return Anime::select(['animes.*', DB::raw('DATE(' . Episode::TABLE_NAME . '.started_at) as grouping_date')])
            ->join(Season::TABLE_NAME, 'animes.id', '=', Season::TABLE_NAME . '.anime_id')
            ->join(Episode::TABLE_NAME, 'seasons.id', '=', Episode::TABLE_NAME . '.season_id')
            ->where(function ($query) use ($dateRanges) {
                foreach ($dateRanges as $range) {
                    $query->orWhereBetween(Episode::TABLE_NAME . '.started_at', [$range['start'], $range['end']]);
                }
            })
            ->orderBy('air_time')
            ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
            ->when($user, function ($query) use ($user) {
                $query->with([
                    'mediaRatings' => fn($q) => $q->where('user_id', $user->id),
                    'library' => fn($q) => $q->where('user_id', $user->id),
                ])
                    ->withExists([
                        'favoriters as isFavorited' => fn($q) => $q->where('user_id', $user->id),
                        'reminderers as isReminded' => fn($q) => $q->where('user_id', $user->id),
                    ]);
            });
    }

    private function queryMangaSchedules(array $dateRanges)
    {
        return Manga::query()
            ->select(['mangas.*', DB::raw('DAYOFWEEK(publication_day) as grouping_date')])
            ->where('status_id', 8)
            ->whereIn('publication_day', array_column($dateRanges, 'dayOfWeek'))
            ->orderBy('publication_time');
    }

    private function queryGameSchedules(array $dateRanges)
    {
        return Game::query()
            ->select(['games.*', DB::raw('DATE(published_at) as grouping_date')])
            ->whereIn('published_at', array_column($dateRanges, 'date'));
    }
}
