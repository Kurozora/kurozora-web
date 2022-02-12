<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Resources\AnimeCastResource;
use App\Models\AnimeCast;
use Illuminate\Http\JsonResponse;

class AnimeCastController extends Controller
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
            'data' => AnimeCastResource::collection([$cast])
        ]);
    }
}
