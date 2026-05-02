<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteNotificationsRequest;
use App\Http\Requests\UpdateNotificationsRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Exception;
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
     * @return JsonResponse
     * @throws Exception
     */
    public function deleteOne(Notification $notification): JsonResponse
    {
        $notification->delete();

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
        $targetedNotification = $request->get('notification');
        $notificationQuery = $request->user()->notifications();

        if ($targetedNotification != 'all') {
            $notificationIDs = explode(',', $targetedNotification);

            if (!count($notificationIDs)) {
                throw new ConflictHttpException('No notifications were specified.');
            }

            $notificationQuery->whereIn('id', $notificationIDs);
        }

        $notificationQuery->delete();

        return JSONResult::success();
    }

    /**
     * Updates a single, multiple, or all notifications' status of the authenticated user.
     *
     * @param UpdateNotificationsRequest $request
     * @return JsonResponse
     * @throws ConflictHttpException
     */
    public function update(UpdateNotificationsRequest $request): JsonResponse
    {
        $markAsRead = (bool) $request->input('read');
        $targetedNotification = $request->get('notification');
        $notificationQuery = $request->user()->notifications();

        if ($targetedNotification != 'all') {
            $notificationIDs = explode(',', $targetedNotification);

            if (!count($notificationIDs)) {
                throw new ConflictHttpException('No notifications were specified.');
            }

            $notificationQuery->whereIn('id', $notificationIDs);
        }

        $notificationQuery->update([
            'read_at' => $markAsRead ? now() : null
        ]);

        return JSONResult::success([
            'data' => [
                'isRead' => $markAsRead
            ]
        ]);
    }
}
