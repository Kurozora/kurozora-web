<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\ScrobbleHistoryRequest;
use App\Http\Requests\ScrobbleRequest;
use App\Http\Resources\EpisodeResourceIdentity;
use App\Services\ScrobbleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ScrobbleController extends Controller
{
    /**
     * Begins or resumes a watch session.
     *
     * @param ScrobbleRequest $request
     * @param ScrobbleService $scrobbleService
     *
     * @return JsonResponse
     */
    public function start(ScrobbleRequest $request, ScrobbleService $scrobbleService): JsonResponse
    {
        $result = $scrobbleService->start(auth()->user(), $request->validated());

        return $this->scrobbleResponse($result);
    }

    /**
     * Saves the session's resume position.
     *
     * @param ScrobbleRequest $request
     * @param ScrobbleService $scrobbleService
     *
     * @return JsonResponse
     */
    public function pause(ScrobbleRequest $request, ScrobbleService $scrobbleService): JsonResponse
    {
        $result = $scrobbleService->pause(auth()->user(), $request->validated());

        return $this->scrobbleResponse($result);
    }

    /**
     * Commits the play as watched when it crossed the threshold, else pauses it.
     *
     * @param ScrobbleRequest $request
     * @param ScrobbleService $scrobbleService
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function stop(ScrobbleRequest $request, ScrobbleService $scrobbleService): JsonResponse
    {
        $result = $scrobbleService->stop(auth()->user(), $request->validated());

        return $this->scrobbleResponse($result);
    }

    /**
     * Commits a batch of dated plays.
     *
     * @param ScrobbleHistoryRequest $request
     * @param ScrobbleService        $scrobbleService
     *
     * @return JsonResponse
     */
    public function history(ScrobbleHistoryRequest $request, ScrobbleService $scrobbleService): JsonResponse
    {
        $result = $scrobbleService->history(auth()->user(), $request->validated()['plays']);

        return JSONResult::success([
            'data' => [
                'attributes' => $result['attributes'],
                'relationships' => [
                    'episodes' => [
                        'data' => EpisodeResourceIdentity::collection($result['episodeIDs']),
                    ],
                ],
            ],
        ]);
    }

    /**
     * Cancels the active "now watching" presence.
     *
     * @param ScrobbleService $scrobbleService
     *
     * @return JsonResponse
     */
    public function cancel(ScrobbleService $scrobbleService): JsonResponse
    {
        $scrobbleService->cancel(auth()->user());

        return JSONResult::success();
    }

    /**
     * Wraps a scrobble result in the response envelope.
     *
     * @param array $result
     *
     * @return JsonResponse
     */
    protected function scrobbleResponse(array $result): JsonResponse
    {
        if ($result['isPending'] ?? false) {
            return JSONResult::success([
                'data' => [
                    'attributes' => $result['attributes'],
                ],
            ])->setStatusCode(202);
        }

        return JSONResult::success([
            'data' => [
                'attributes' => $result['attributes'],
                'relationships' => [
                    'episodes' => [
                        'data' => EpisodeResourceIdentity::collection([$result['episode']->public_id]),
                    ],
                ],
            ],
        ]);
    }
}
