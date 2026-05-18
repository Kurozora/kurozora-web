<?php

namespace App\Http\Controllers\Nova;

use App\Http\Controllers\Controller;
use App\Services\Presence\PresenceTracker;
use Illuminate\Http\JsonResponse;

class PresenceSeedController extends Controller
{
    /**
     * Returns the current live counts of signed-in users and guest contexts.
     *
     * @param PresenceTracker $tracker
     *
     * @return JsonResponse
     */
    public function show(PresenceTracker $tracker): JsonResponse
    {
        return response()->json([
            'web' => [
                'signed_in' => $tracker->count(PresenceTracker::BUCKET_WEB_USERS),
                'guests' => $tracker->count(PresenceTracker::BUCKET_WEB_GUESTS),
            ],
            'api' => [
                'signed_in' => $tracker->count(PresenceTracker::BUCKET_API_USERS),
                'guests' => $tracker->count(PresenceTracker::BUCKET_API_GUESTS),
            ],
            'at' => now()->toIso8601String(),
        ]);
    }
}
