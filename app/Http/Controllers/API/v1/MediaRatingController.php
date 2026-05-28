<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Resources\MediaRatingResource;
use App\Models\Episode;
use App\Models\MediaRating;
use Illuminate\Http\JsonResponse;

class MediaRatingController extends Controller
{
    /**
     * Shows song details.
     *
     * @param MediaRating $mediaRating
     * @return JsonResponse
     */
    public function details(MediaRating $mediaRating): JsonResponse
    {
        $mediaRating->load([
            'user' => function ($query) {
                $query->withProfileEagerLoad(auth()->user());
            },
        ]);

        if ($mediaRating->model_type === Episode::class) {
            $mediaRating->episode_public_id = Episode::withoutGlobalScopes()
                ->whereKey($mediaRating->model_id)
                ->value('public_id');
        }

        return JSONResult::success([
            'data' => MediaRatingResource::collection([$mediaRating])
        ]);
    }

    /**
     * Delete the given media rating.
     *
     * @param MediaRating $mediaRating
     *
     * @return JsonResponse
     */
    public function delete(MediaRating $mediaRating)
    {
        $mediaRating->forceDelete();

        return JSONResult::success();
    }
}
