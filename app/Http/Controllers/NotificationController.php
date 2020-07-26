<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\UpdateUserNotifications;
use App\Http\Resources\NotificationResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Retrieves details for a specific notification
     *
     * @param DatabaseNotification $notification
     * @return JsonResponse
     */
    public function details(DatabaseNotification $notification) {
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
    public function delete(DatabaseNotification $notification) {
        // Delete the notification
        $notification->delete();

        return JSONResult::success();
    }

    /**
     * Updates a single, multiple or all notifications' status.
     *
     * @param UpdateUserNotifications $request
     * @return JsonResponse
     */
    public function update(UpdateUserNotifications $request) {
        /*
         * TODO:
         * This does NOT yet check whether or not the user is allowed to update the notification.
         */
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
                return JSONResult::error('No notifications were specified.');

            // Update the notifications
            $notificationQuery->whereIn('id', $notificationIDs);
        }

        // Update the notifications
        $amountUpdated = $notificationQuery->update([
            'read_at' => $markAsRead ? now() : null
        ]);

        return JSONResult::success([
            'read'              => $markAsRead,
            'amount_updated'    => $amountUpdated
        ]);
    }
}
