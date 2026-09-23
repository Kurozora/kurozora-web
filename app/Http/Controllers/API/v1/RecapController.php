<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Resources\RecapItemResource;
use App\Http\Resources\RecapResource;
use App\Http\Resources\RecapStatResource;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\Provider;
use App\Models\Recap;
use App\Models\RecapStat;
use App\Models\Theme;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;

class RecapController extends Controller
{
    /**
     * Return an overview of recaps.
     *
     * @return JsonResponse
     */
    function index(): JsonResponse
    {
        $recaps = auth()->user()->recaps()
            ->selectRaw('MAX(id) as id, year, month')
            ->distinct()
            ->orderBy('year', 'desc')
            ->orderBy('month')
            ->groupBy(['year', 'month'])
            ->get();

        $hasCurrentMonth = $recaps->contains(function (Recap $recap) {
            return $recap->year === now()->year && $recap->month === now()->month;
        });

        if (!$hasCurrentMonth) {
            $recaps->push(Recap::make([
                'id' => 0,
                'year' => now()->year,
                'month' => now()->month,
            ]));
        }

        $recaps = $recaps->sortBy([
            ['year', 'desc'],
            ['month', 'asc'],
        ])->values();

        return JSONResult::success([
            'data' => RecapResource::collection($recaps)
        ]);
    }

    /**
     * Returns detailed Recap information.
     *
     * @param int|string $year
     *
     * @return JsonResponse
     */
    // MARK: - Remove after 1.11.0
    public function oldView(int|string $year): JsonResponse
    {
        if ($year == now()->year) {
            $month = now()->subMonth()->month;
        } else {
            $month = 0;
        }

        $recaps = auth()->user()->recaps()
            ->whereDisplayableType()
            ->with(['recapItems.model'])
            ->where('year', '=', $year)
            ->where('month', '=', $month)
            ->get();

        return JSONResult::success([
            'data' => RecapItemResource::collection($recaps)
        ]);
    }

    /**
     * Returns detailed Re:CAP information.
     *
     * @param int|string $year
     * @param int|string $month
     *
     * @return JsonResponse
     */
    public function view(int|string $year, int|string $month): JsonResponse
    {
        $recaps = auth()->user()->recaps()
            ->with([
                'recapItems.role',
                'recapItems.model' => function (MorphTo $morphTo) {
                    $morphTo->withoutGlobalScopes()
                        ->constrain([
                            Anime::class => function (Builder $query) {
                                $query->with(['genres', 'mediaStat', 'media', 'translation', 'tvRating', 'themes']);
                            },
                            Game::class => function (Builder $query) {
                                $query->with(['genres', 'mediaStat', 'media', 'translation', 'tvRating', 'themes']);
                            },
                            Genre::class => function (Builder $query) {
                                $query->with(['media']);
                            },
                            Manga::class => function (Builder $query) {
                                $query->with(['genres', 'mediaStat', 'media', 'translation', 'tvRating', 'themes']);
                            },
                            Theme::class => function (Builder $query) {
                                $query->with(['media']);
                            },
                        ]);
                }
            ])
            ->where('year', '=', $year)
            ->where('month', '=', $month)
            ->get();

        return JSONResult::success([
            'data' => RecapItemResource::collection($recaps)
        ]);
    }

    /**
     * Returns the Re:CAP stats of the given period.
     *
     * @param int|string $year
     * @param int|string $month
     *
     * @return JsonResponse
     */
    public function stats(int|string $year, int|string $month): JsonResponse
    {
        $recapStats = RecapStat::where([
            ['user_id', '=', auth()->id()],
            ['year', '=', $year],
            ['month', '=', $month],
        ])
            ->with([
                'model' => function (MorphTo $morphTo) {
                    $morphTo->constrain([
                        Provider::class => function (Builder $query) {
                            $query->with(['media']);
                        },
                    ]);
                },
            ])
            ->orderBy('stat')
            ->get();

        return JSONResult::success([
            'data' => RecapStatResource::collection($recapStats)
        ]);
    }
}
