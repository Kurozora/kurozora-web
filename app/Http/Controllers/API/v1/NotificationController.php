<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\JSONResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateNotificationsRequest;
use App\Http\Resources\NotificationResource;
use App\Models\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;
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
     * @param DatabaseNotification $databaseNotification
     * @return JsonResponse
     */
    public function details(DatabaseNotification $databaseNotification): JsonResponse
    {
        return JSONResult::success([
            'data' => NotificationResource::make($databaseNotification)
        ]);
    }

    /**
     * Deletes the authenticated user's notification.
     *
     * @param DatabaseNotification $databaseNotification
     * @return JsonResponse
     * @throws Exception
     */
    public function delete(DatabaseNotification $databaseNotification): JsonResponse
    {
        // Delete the notification
        $databaseNotification->delete();

        return JSONResult::success();
    }

    /**
     * Updates a single, multiple or all notifications' status of the authenticated user.
     *
     * @param UpdateNotificationsRequest $request
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws ConflictHttpException
     */
    public function update(UpdateNotificationsRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $markAsRead = (bool) $request->input('read');

        // Get the notification(s) the user is targeting to update
        $targetedNotification = $request->get('notification');

        // User wants to update all of their notifications
        $notificationQuery = $request->user()->notifications();

        if ($targetedNotification != 'all') {
            // Explode the string. This leaves an array of IDs
            $notificationIDs = explode(',', $targetedNotification);

            // Make sure there are items in the array
            if (!count($notificationIDs))
                throw new ConflictHttpException('No notifications were specified.');

            // Make sure the notifications belong to the currently authenticated user
            foreach ($notificationIDs as $notificationID) {
                if (!$user->notifications->contains($notificationID)) {
                    throw new AuthorizationException(__('The request wasn’t accepted due to an issue with the notifications or because it’s using incorrect authentication.'));
                }
            }

            // Get the notifications to be updated
            $notificationQuery->whereIn('id', $notificationIDs);
        }

        // Update the notifications
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
