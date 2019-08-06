<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\Http\Requests\UpdateUserNotifications;
use App\Http\Resources\UserNotificationResource;
use App\UserNotification;
use Illuminate\Http\JsonResponse;

class UserNotificationController extends Controller
{
    /**
     * Retrieves details for a specific notification
     *
     * @param UserNotification $notification
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function getNotification(UserNotification $notification) {
        return JSONResult::success([
            'notification' => UserNotificationResource::make($notification)
        ]);
    }

    /**
     * Deletes a user's notification
     *
     * @param UserNotification $notification
     * @return JsonResponse
     * @throws \Exception
     */
    public function delete(UserNotification $notification) {
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
         * Transfers the "status" parameter to a boolean
         * true = read
         * false = unread
         */
        $readBoolean = $request->get('status') == 'read' ? true : false;

        // Get the notification(s) the user is targeting to update
        $targetedNotification = $request->get('notification');

        // User wants to update all of their notifications
        if($targetedNotification == 'all') {
            $request->user()->notifications()->update([
                'read' => $readBoolean
            ]);
        }
        else {
            // Explode the string. This leaves an array of IDs
            $notificationIDs = explode(',', $targetedNotification);

            // Make sure there are items in the array
            if(!count($notificationIDs))
                return JSONResult::error('No notifications were specified.');

            // Loop through the array and make sure every item is a number (ID)
            foreach($notificationIDs as $notificationID) {
                if(!is_numeric($notificationID))
                    return JSONResult::error('Only a set of IDs is allowed.');
            }

            // Update the notifications
            $request->user()->notifications()->whereIn('id', $notificationIDs)->update([
                'read' => $readBoolean
            ]);
        }

        return JSONResult::success();
    }
}
