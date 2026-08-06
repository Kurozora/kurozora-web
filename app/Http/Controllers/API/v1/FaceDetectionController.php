<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFaceDetectionsBatchRequest;
use App\Models\Media;
use Illuminate\Http\JsonResponse;

class FaceDetectionController extends Controller
{
    /**
     * Stores a batch of face focal points on the corresponding media.
     *
     * @param StoreFaceDetectionsBatchRequest $request
     * @return JsonResponse
     */
    public function batch(StoreFaceDetectionsBatchRequest $request): JsonResponse
    {
        $entries = $request->input('data', []);

        foreach ($entries as $entry) {
            $media = Media::find($entry['media_id']);

            if ($media === null) {
                continue;
            }

            $focalX = $entry['focal_x'] ?? null;
            $focalY = $entry['focal_y'] ?? null;

            $media->setCustomProperty('focal_x', $focalX !== null ? (float) $focalX : null);
            $media->setCustomProperty('focal_y', $focalY !== null ? (float) $focalY : null);
            $media->setCustomProperty('face_detector_id', (string) $entry['detector_id']);
            $media->save();
        }

        return JSONResult::success();
    }
}
