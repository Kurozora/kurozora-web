<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\MediaType;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetRandomImageRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\JsonResponse;

class ImageController extends Controller
{
    /**
     * Generate an overview of genres.
     *
     * @param GetRandomImageRequest $request
     *
     * @return JsonResponse
     */
    public function random(GetRandomImageRequest $request): JsonResponse
    {
        $data = $request->validated();
        $mediaType = MediaType::fromValue($data['type']);
        $modelType = $mediaType->toModel();

        // Get random images
        $images = Media::where('collection_name', '=', $data['collection'])
            ->where('model_type', '=', $modelType)
            ->whereMorphRelation('model', [$modelType], function ($query) {
                $query->where([
                    ['tv_rating_id', '<=', 4],
                    ['is_nsfw', '=', false],
                ]);
            })
            ->limit($data['limit'] ?? 1)
            ->inRandomOrder()
            ->get();

        // Show images in response
        return JSONResult::success([
            'data' => MediaResource::collection($images)
        ]);
    }
}
