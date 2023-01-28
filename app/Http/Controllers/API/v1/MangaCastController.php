<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Resources\MangaCastResource;
use App\Models\MangaCast;
use Illuminate\Http\JsonResponse;

class MangaCastController extends Controller
{
    /**
     * Shows cast details.
     *
     * @param MangaCast $cast
     * @return JsonResponse
     */
    public function details(MangaCast $cast): JsonResponse
    {
        // Return cast details
        return JSONResult::success([
            'data' => MangaCastResource::collection([$cast])
        ]);
    }
}
