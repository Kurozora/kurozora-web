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
                    $query->with([
                        'badges' => function ($query) {
                            $query->with(['media']);
                        },
                        'media',
                        'tokens' => function ($query) {
                            $query
                                ->orderBy('last_used_at', 'desc')
                                ->limit(1);
                        },
                        'sessions' => function ($query) {
                            $query
                                ->orderBy('last_activity', 'desc')
                                ->limit(1);
                        },
                    ])
                        ->withCount(['followers', 'following', 'mediaRatings']);
                },
            ]);

        return JSONResult::success([
            'data' => MediaRatingResource::collection([$mediaRating])
        ]);
    }
}
