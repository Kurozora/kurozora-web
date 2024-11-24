<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShowCastResource;
use App\Models\AnimeCast;
use Illuminate\Http\JsonResponse;

class ShowCastController extends Controller
{
    /**
     * Shows cast details.
     *
     * @param AnimeCast $cast
     * @return JsonResponse
     */
    public function details(AnimeCast $cast): JsonResponse
    {
        $cast->load([
            'person' => function ($query) {
                $query->with(['media']);
            },
            'character' => function ($query) {
                $query->with(['media', 'translation']);
            },
            'castRole',
            'language'
        ]);

        // Return cast details
        return JSONResult::success([
            'data' => ShowCastResource::collection([$cast])
        ]);
    }
}
