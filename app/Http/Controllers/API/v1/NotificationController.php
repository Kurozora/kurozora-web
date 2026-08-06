<?php

namespace App\Http\Controllers\API\v1;

use App\Events\Notifications\NotificationDeleted;
use App\Events\Notifications\NotificationRead;
use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteNotificationsRequest;
use App\Http\Requests\UpdateNotificationsRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class NotificationController extends Controller
{
    /**
     * Returns the notifications for the authenticated user.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        return JSONResult::success([
            'data' => NotificationResource::collection($user->notifications)
        ]);
    }

    /**
     * Retrieves details for a specific notification.
     *
     * @param Notification $notification
     *
     * @return JsonResponse
     */
    public function details(Notification $notification): JsonResponse
    {
        return JSONResult::success([
            'data' => NotificationResource::make($notification)
        ]);
    }

    /**
     * Deletes a single notification belonging to the authenticated user.
     *
     * @param Notification $notification
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function deleteOne(Notification $notification): JsonResponse
    {
        $userID = $notification->notifiable_id;
        $notificationID = (string) $notification->getKey();

        $notification->delete();

        broadcast(new NotificationDeleted($userID, [$notificationID]))
            ->toOthers();

        return JSONResult::success();
    }

    /**
     * Deletes a single, multiple, or all notifications of the authenticated user.
     *
     * @param DeleteNotificationsRequest $request
     *
     * @return JsonResponse
     * @throws ConflictHttpException
     * @throws Exception
     */
    public function delete(DeleteNotificationsRequest $request): JsonResponse
    {
        $user = $request->user();
        [$query, $broadcastIDs] = $this->resolveTargetedNotifications($request, $user);

        $query->delete();

        broadcast(new NotificationDeleted((int) $user->getKey(), $broadcastIDs))
            ->toOthers();

        return JSONResult::success();
    }

    /**
     * Updates a single, multiple, or all notifications' status of the authenticated user.
     *
     * @param UpdateNotificationsRequest $request
     *
     * @return JsonResponse
     * @throws ConflictHttpException
     */
    public function update(UpdateNotificationsRequest $request): JsonResponse
    {
        $user = $request->user();
        $markAsRead = $request->boolean('read');
        [$query, $broadcastIDs] = $this->resolveTargetedNotifications($request, $user);

        $query->update([
            'read_at' => $markAsRead ? now() : null,
        ]);

        broadcast(new NotificationRead((int) $user->getKey(), $broadcastIDs, $markAsRead))
            ->toOthers();

        return JSONResult::success([
            'data' => [
                'isRead' => $markAsRead,
            ],
        ]);
    }

    /**
     * Resolves the `notification` request parameter into a constrained query and the matching broadcast id list.
     *
     * @param DeleteNotificationsRequest|UpdateNotificationsRequest $request
     * @param User                                                  $user
     *
     * @return array{0: Relation, 1: array<string>|string}
     *
     * @throws ConflictHttpException
     */
    private function resolveTargetedNotifications(DeleteNotificationsRequest|UpdateNotificationsRequest $request, User $user): array
    {
        $target = (string) $request->input('notification');
        $query = $user->notifications();

        if ($target === 'all') {
            return [$query, 'all'];
        }

        $notificationIDs = array_values(array_filter(explode(',', $target)));

        if (empty($notificationIDs)) {
            throw new ConflictHttpException('No notifications were specified.');
        }

        return [$query->whereIn('id', $notificationIDs), $notificationIDs];
    }
}
