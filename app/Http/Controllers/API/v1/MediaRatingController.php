<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Resources\MediaRatingResource;
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
        // Get the feed messages
        $mediaRating = $mediaRating
            ->load([
                'user'=> function ($query) {
                    $query->withProfileEagerLoad(auth()->user());
                },
            ]);

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
