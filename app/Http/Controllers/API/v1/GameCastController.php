<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRequest;
use App\Http\Resources\GameCastResource;
use App\Models\GameCast;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class GameCastController extends Controller
{
    /**
     * Returns the cast index.
     *
     * @param GetIndexRequest $request
     *
     * @return JsonResponse
     */
    public function index(GetIndexRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['ids'])) {
            return $this->views($request);
        }

        throw new ValidationException("The 'ids' parameter is required for this endpoint.");
    }

    /**
     * Returns detailed information of requested IDs.
     *
     * @param GetIndexRequest $request
     *
     * @return JsonResponse
     */
    public function views(GetIndexRequest $request): JsonResponse
    {
        $data = $request->validated();

        $cast = GameCast::whereIn('id', $data['ids'])
            ->with([
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
            'data' => GameCastResource::collection($cast->get())
        ]);
    }

    /**
     * Games cast details.
     *
     * @param GameCast $cast
     *
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
