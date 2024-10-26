<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\MediaType;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetRandomImageRequest;
use App\Http\Resources\ImageResource;
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
        $images = Media::with('model')
            ->where([
                ['collection_name', '=', $data['collection']],
                ['model_type', '=', $modelType],
            ])
            ->whereMorphRelation('model', [$modelType], function ($query) use ($modelType) {
                $query->where([
                    ['tv_rating_id', '<=', 4],
                    ['is_nsfw', '=', false],
                ])
                    ->whereRaw('`' . $modelType::TABLE_NAME . '`.`id` >= FLOOR(RAND() * (SELECT MAX(`' . $modelType::TABLE_NAME . '`.`id`) FROM `' . $modelType::TABLE_NAME . '`))');
            })
            ->limit($data['limit'] ?? 10)
            ->get();

        // Show images in response
        return JSONResult::success([
            'data' => ImageResource::collection($images)
        ]);
    }
}
