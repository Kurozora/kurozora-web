<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetIndexRequest;
use App\Http\Resources\LiteratureCastResource;
use App\Models\MangaCast;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LiteratureCastController extends Controller
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

        $cast = MangaCast::whereIn('id', $data['ids'])
            ->with([
                'character' => function ($query) {
                    $query->with(['media', 'translation']);
                },
                'castRole'
            ]);

        // Return cast details
        return JSONResult::success([
            'data' => LiteratureCastResource::collection($cast->get())
        ]);
    }

    /**
     * Literatures cast details.
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
