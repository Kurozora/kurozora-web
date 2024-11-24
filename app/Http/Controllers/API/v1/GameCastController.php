<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Resources\GameCastResource;
use App\Models\GameCast;
use Illuminate\Http\JsonResponse;

class GameCastController extends Controller
{
    /**
     * Shows cast details.
     *
     * @param GameCast $cast
     * @return JsonResponse
     */
    public function details(GameCast $cast): JsonResponse
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
            'data' => GameCastResource::collection([$cast])
        ]);
    }
}
