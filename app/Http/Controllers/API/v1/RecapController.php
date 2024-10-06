<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Resources\RecapItemResource;
use App\Http\Resources\RecapResource;
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
            ->distinct()
            ->orderBy('year', 'desc')
            ->select(['id', 'year'])
            ->get();

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
    public function view(int|string $year): JsonResponse
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
}
