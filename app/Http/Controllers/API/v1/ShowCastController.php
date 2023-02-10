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
        // Return cast details
        return JSONResult::success([
            'data' => ShowCastResource::collection([$cast])
        ]);
    }
}
