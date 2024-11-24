<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Resources\LiteratureCastResource;
use App\Models\MangaCast;
use Illuminate\Http\JsonResponse;

class LiteratureCastController extends Controller
{
    /**
     * Shows cast details.
     *
     * @param MangaCast $cast
     * @return JsonResponse
     */
    public function details(MangaCast $cast): JsonResponse
    {
        $cast->load([
            'character' => function ($query) {
                $query->with(['media', 'translation']);
            },
            'castRole'
        ]);

        // Return cast details
        return JSONResult::success([
            'data' => LiteratureCastResource::collection([$cast])
        ]);
    }
}
