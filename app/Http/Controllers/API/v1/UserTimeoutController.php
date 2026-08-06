<?php

namespace App\Http\Controllers\API\v1;

use App\Enums\TimeoutDuration;
use App\Helpers\JSONResult;
use App\Http\Requests\Moderation\IssueTimeoutRequest;
use App\Http\Resources\TimeoutResource;
use App\Models\User;
use App\Notifications\UserTimedOut;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserTimeoutController
{
    /**
     * Issues a moderation timeout against the given user.
     *
     * @param IssueTimeoutRequest $request
     * @param User                $user
     *
     * @return JsonResponse
     */
    public function store(IssueTimeoutRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        $issuer = $request->user();

        if ($issuer->id === $user->id) {
            throw new BadRequestHttpException('You cannot issue a timeout against yourself.');
        }

        if ($user->hasAnyRole(['superAdmin', 'admin', 'mod'])) {
            throw new BadRequestHttpException('Staff accounts cannot be timed out from the API.');
        }

        $duration = (int) $data['duration'];
        $reasonKey = (int) $data['reason_key'];
        $isPermanent = $duration === TimeoutDuration::Permanent;
        $newExpiresAt = TimeoutDuration::expiresAtFor($duration);

        $timeout = DB::transaction(function () use ($user, $issuer, $data, $reasonKey, &$isPermanent, &$newExpiresAt) {
            $previousTimeout = $user->timeouts()
                ->active()
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($previousTimeout !== null) {
                if ($previousTimeout->is_permanent) {
                    $isPermanent = true;
                    $newExpiresAt = null;
                } else if (!$isPermanent && $previousTimeout->expires_at !== null && $newExpiresAt !== null && $previousTimeout->expires_at->greaterThan($newExpiresAt)) {
                    $newExpiresAt = $previousTimeout->expires_at->toImmutable();
                }

                $previousTimeout->update([
                    'revoked_at' => Carbon::now(),
                    'revoked_by_id' => $issuer->id,
                ]);
            }

            return $user->timeouts()->create([
                'issued_by_id' => $issuer->id,
                'reason_key' => $reasonKey,
                'note' => $data['note'] ?? null,
                'is_permanent' => $isPermanent,
                'expires_at' => $isPermanent ? null : $newExpiresAt,
            ]);
        });

        $user->notify(new UserTimedOut($timeout));

        return JSONResult::success([
            'data' => [
                TimeoutResource::make($timeout->load('appeal')),
            ],
        ]);
    }

    /**
     * Revokes the active timeout on the given user.
     *
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $issuer = $request->user();

        $revoked = DB::transaction(function () use ($user, $issuer) {
            $activeTimeout = $user->timeouts()
                ->active()
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($activeTimeout === null) {
                return false;
            }

            $activeTimeout->update([
                'revoked_at' => Carbon::now(),
                'revoked_by_id' => $issuer->id,
            ]);

            return true;
        });

        if (!$revoked) {
            throw new NotFoundHttpException('No active timeout was found for this user.');
        }

        return JSONResult::success();
    }

    /**
     * Returns the active timeout on the given user.
     *
     * @param Request $request
     * @param User    $user
     *
     * @return JsonResponse
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $activeTimeout = $user->timeouts()->active()->with('appeal')->latest('id')->first();

        if ($activeTimeout === null) {
            throw new NotFoundHttpException('No active timeout was found for this user.');
        }

        return JSONResult::success([
            'data' => [
                TimeoutResource::make($activeTimeout),
            ],
        ]);
    }
}
