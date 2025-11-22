<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRequest;
use App\Http\Resources\ShowCastResource;
use App\Models\AnimeCast;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ShowCastController extends Controller
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

        $cast = AnimeCast::whereIn('id', $data['ids'])
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
            'data' => ShowCastResource::collection($cast->get())
        ]);
    }

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
