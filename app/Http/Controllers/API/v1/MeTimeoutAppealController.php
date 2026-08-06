<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Requests\Moderation\AppealTimeoutRequest;
use App\Http\Resources\TimeoutResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MeTimeoutAppealController
{
    /**
     * Files an appeal against the authenticated user's active timeout.
     *
     * @param AppealTimeoutRequest $request
     *
     * @return JsonResponse
     */
    public function store(AppealTimeoutRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $activeTimeout = $user->timeouts()->active()->with('appeal')->latest('id')->first();

        if ($activeTimeout === null) {
            throw new NotFoundHttpException('You do not have an active timeout to appeal.');
        }

        if ($activeTimeout->appeal !== null) {
            throw new ConflictHttpException('You have already appealed this timeout.');
        }

        $activeTimeout->appeal()->create([
            'message' => $data['message'],
        ]);

        return JSONResult::success([
            'data' => [
                TimeoutResource::make($activeTimeout->load('appeal')),
            ],
        ]);
    }

    /**
     * Updates the authenticated user's existing appeal.
     *
     * @param AppealTimeoutRequest $request
     *
     * @return JsonResponse
     */
    public function update(AppealTimeoutRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $activeTimeout = $user->timeouts()->active()->with('appeal')->latest('id')->first();

        if ($activeTimeout === null) {
            throw new NotFoundHttpException('You do not have an active timeout to appeal.');
        }

        if ($activeTimeout->appeal === null) {
            throw new NotFoundHttpException('You have not filed an appeal to edit yet.');
        }

        $activeTimeout->appeal->update([
            'message' => $data['message'],
        ]);

        return JSONResult::success([
            'data' => [
                TimeoutResource::make($activeTimeout->load('appeal')),
            ],
        ]);
    }
}
