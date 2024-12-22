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

        $schedule = $this->fetchSchedule($model, $dateRanges);

        return JSONResult::success([
            'data' => ScheduleResource::collection($schedule),
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

    private function fetchSchedule(string $model, array $dateRanges): array
    {
        $query = match ($model) {
            Anime::class => $this->queryAnimeSchedule($dateRanges),
            Manga::class => $this->queryMangaSchedule($dateRanges),
            Game::class => $this->queryGameSchedule($dateRanges),
            default => throw new InvalidArgumentException('Unsupported model type'),
        };

        return $query->get()
            ->groupBy('grouping_date')
            ->map(fn($models, $groupingDate) => [
                'date' => $groupingDate,
                'type' => $model,
                'models' => $models,
            ])
            ->sortBy('date')
            ->values()
            ->toArray();
    }

    private function queryAnimeSchedule(array $dateRanges)
    {
        return Anime::withSchedule($dateRanges)
            ->select([Anime::TABLE_NAME . '.*', DB::raw('DATE(' . Episode::TABLE_NAME . '.started_at) as grouping_date')])
            ->groupBy('grouping_date') // scope already includes grouping on id
            ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
            ->when(auth()->user(), function ($query, $user) {
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

    private function queryMangaSchedule(array $dateRanges)
    {
        return Manga::withSchedule(array_column($dateRanges, 'dayOfWeek'))
            ->select([Manga::TABLE_NAME . '.*', DB::raw('DAYOFWEEK(started_at) as grouping_date')])
            ->groupBy('grouping_date') // scope already includes grouping on id
            ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
            ->when(auth()->user(), function ($query, $user) {
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

    private function queryGameSchedule(array $dateRanges)
    {
        return Game::withSchedule(array_column($dateRanges, 'date'))
            ->select([Game::TABLE_NAME . '.*', DB::raw('DATE(published_at) as grouping_date')])
            ->groupBy('grouping_date') // scope already includes grouping on id
            ->with(['genres', 'languages', 'media', 'mediaStat', 'media_type', 'source', 'status', 'studios', 'themes', 'translation', 'tv_rating', 'country_of_origin'])
            ->when(auth()->user(), function ($query, $user) {
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
}
