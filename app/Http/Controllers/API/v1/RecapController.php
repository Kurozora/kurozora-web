<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
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
            ->pluck('year');

        return JSONResult::success([
            'data' => $recaps
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
        $recaps = auth()->user()->recaps()
            ->with(['recapItems.model'])
            ->where('year', '=', $year)
            ->get();

        return JSONResult::success([
            'data' => RecapResource::collection($recaps)
        ]);
    }
}
