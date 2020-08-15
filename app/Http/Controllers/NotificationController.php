<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\UpdateUserNotifications;
use App\Http\Resources\NotificationResource;
use App\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class NotificationController extends Controller
{
    /**
     * Retrieves details for a specific notification
     *
     * @param DatabaseNotification $notification
     * @return JsonResponse
     */
    public function details(DatabaseNotification $notification): JsonResponse
    {
        return JSONResult::success([
            'data' => NotificationResource::make($notification)
        ]);
    }

    /**
     * Deletes a user's notification
     *
     * @param DatabaseNotification $notification
     * @return JsonResponse
     * @throws Exception
     */
    public function delete(DatabaseNotification $notification): JsonResponse
    {
        // Delete the notification
        $notification->delete();

        return JSONResult::success();
    }

    /**
     * Updates a single, multiple or all notifications' status.
     *
     * @param UpdateUserNotifications $request
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws ConflictHttpException
     */
    public function update(UpdateUserNotifications $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $markAsRead = (bool) $request->input('read');

        // Get the notification(s) the user is targeting to update
        $targetedNotification = $request->get('notification');

        // User wants to update all of their notifications
        $notificationQuery = $request->user()->notifications();

        if($targetedNotification != 'all') {
            // Explode the string. This leaves an array of IDs
            $notificationIDs = explode(',', $targetedNotification);

            // Make sure there are items in the array
            if(!count($notificationIDs))
                throw new ConflictHttpException('No notifications were specified.');

            // Make sure the notifications belong to the currently authenticated user
            foreach ($notificationIDs as $notificationID) {
                if (!$user->notifications->contains($notificationID)) {
                    throw new AuthorizationException('The request wasn’t accepted due to an issue with the notifications or because it’s using incorrect authentication.');
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
