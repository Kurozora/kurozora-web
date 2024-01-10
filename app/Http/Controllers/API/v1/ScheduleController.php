<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\UserLibraryKind;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Anime;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

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
        $libraryKind = UserLibraryKind::fromValue((int) $data['type']);

        // Get whether user is in import cooldown period
        $model = match ($libraryKind->value) {
            UserLibraryKind::Game => Game::class,
            UserLibraryKind::Manga => Manga::class,
            default => Anime::class
        };

        // Get whether user is in import cooldown period
        $dayKey = match ($model) {
            Game::class, Manga::class => 'publication_day',
            default => 'air_day'
        };
        $schedules = [];

        /** @var Carbon $date */
        foreach ($dates as $date) {
            $schedules[] = [
                'date' => $date->startOfDay()->timestamp,
                'type' => $model,
                'models' => $model::where([
                    [$dayKey, '=', $date->dayOfWeek],
                ])
                    ->when($model == Anime::class, function ($query) use ($date) {
                        $query->whereHas('episodes', function ($query) use ($date) {
                            $query->where([
                                [Episode::TABLE_NAME . '.started_at', '>=', $date->startOfDay()->toDateTimeString()],
                                [Episode::TABLE_NAME . '.started_at', '<=', $date->endOfDay()->toDateTimeString()],
                            ]);
                        })
                            ->orderBy('air_time');
                    })
                    ->when($model == Manga::class, function ($query) use ($date) {
                        $query->where([
                            ['status_id', '=', 8],
                        ])
                            ->orderBy('publication_time');
                    })
                    ->when($model == Game::class, function ($query) use ($date) {
                        $query->where([
                            ['published_at', '=', $date->startOfDay()->toDateString()]
                        ]);
                    })
                    ->pluck('id')
            ];
        }

        return JSONResult::success([
            'data' => ScheduleResource::collection($schedules),
        ]);
    }

    /**
     * Get a week's worth of dates.
     *
     * @param $date
     *
     * @return array
     */
    private function getDates($date): array
    {
        $date = Carbon::createFromFormat('Y-m-d', $date);

        // Get the previous day's date
        $previousDay = $date->copy()->subDay();
        $dateCollection[] = $previousDay;

        // Add the given date to the collection
        $dateCollection[] = $date;

        // Get the next 5 days' dates
        for ($i = 1; $i <= 5; $i++) {
            $nextDay = $date->copy()->addDays($i);
            $dateCollection[] = $nextDay;
        }

        return $dateCollection;
    }
}
