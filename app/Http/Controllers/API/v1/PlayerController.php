<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Resources\PlayerResource;
use App\Models\Player;
use Illuminate\Http\JsonResponse;

class PlayerController extends Controller
{
    /**
     * Returns the players index.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $players = Player::query()
            ->with('media')
            ->orderBy('original_name')
            ->get();

        return JSONResult::success([
            'data' => PlayerResource::collection($players)
        ]);
    }
}
