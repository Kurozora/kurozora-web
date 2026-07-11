<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\WatchedKind;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetWatchedOverlayRequest;
use App\Http\Resources\EpisodeResourceIdentity;
use App\Models\Episode;
use App\Models\User;
use App\Models\UserWatchedEpisode;
use App\Traits\Controller\WithStateVersionETag;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UserWatchedEpisodeController extends Controller
{
    use WithStateVersionETag;

    /**
     * Returns the user's watched-state Resources for the requested episode public IDs.
     *
     * @param GetWatchedOverlayRequest $request
     * @param User                     $user
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function overlay(GetWatchedOverlayRequest $request, User $user): JsonResponse
    {
        if (auth()->id() !== $user->id) {
            throw new AuthorizationException(__('Watched state is currently visible only to its owner.'));
        }

        $data = $request->validated();
        $publicIds = array_values(array_unique($data['ids']));
        sort($publicIds);

        $fingerprint = [
            'kind' => WatchedKind::Episode,
            'ids' => $publicIds,
        ];
        $notModified = $this->returnIfNotModified($request, $user, $fingerprint);
        if ($notModified !== null) {
            return $notModified;
        }
        $etag = $this->stateVersionETag($user, $fingerprint);

        // Resolve episode public IDs to numeric IDs without hydrating Episode models.
        $idToPublicId = DB::table(Episode::TABLE_NAME)
            ->whereIn('public_id', $publicIds)
            ->pluck('public_id', 'id');

        $entries = [];

        if ($idToPublicId->isNotEmpty()) {
            DB::table(UserWatchedEpisode::TABLE_NAME)
                ->where('user_id', '=', $user->id)
                ->whereIn('episode_id', $idToPublicId->keys())
                ->select(['episode_id', 'rewatch_count', 'state', 'progress', 'position', 'completed_at'])
                ->cursor()
                ->each(function ($row) use (&$entries, $idToPublicId) {
                    $publicId = $idToPublicId->get($row->episode_id);

                    if ($publicId === null) {
                        return;
                    }

                    $entries[] = [
                        'attributes' => [
                            'rewatchCount' => (int) $row->rewatch_count,
                            'watchedAt' => $row->completed_at ? Carbon::parse($row->completed_at)->timestamp : null,
                            'state' => (int) $row->state,
                            'progress' => (int) $row->progress,
                            'position' => $row->position !== null ? (int) $row->position : null,
                            'isCompleted' => $row->completed_at !== null,
                        ],
                        'relationships' => [
                            'episodes' => [
                                'data' => EpisodeResourceIdentity::collection([$publicId]),
                            ],
                        ],
                    ];
                });
        }

        return JSONResult::success([
            'data' => $entries,
        ])->withHeaders($this->stateVersionHeaders($etag, $user));
    }
}
