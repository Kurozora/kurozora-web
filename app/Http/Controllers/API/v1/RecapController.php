<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Resources\RecapItemResource;
use App\Http\Resources\RecapResource;
use App\Models\Recap;
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

        if (now()->month !== 12) {
            $recaps->push(Recap::make([
                'id' => 0,
                'year' => now()->year,
                'month' => now()->month,
            ]));
        }

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
            $month = 12;
        }

        $recaps = auth()->user()->recaps()
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
            ->with(['recapItems.model'])
            ->where('year', '=', $year)
            ->where('month', '=', $month)
            ->get();

        return JSONResult::success([
            'data' => RecapItemResource::collection($recaps)
        ]);
    }
}
