<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\UserNotification;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    /**
     * Retrieves details for a specific notification
     *
     * @param Request $request
     * @param $notificationID
     */
    public function get(Request $request, $notificationID) {
        // Find the notification
        $notification = UserNotification::find($notificationID);

        if(!$notification)
            (new JSONResult())->setError(JSONResult::ERROR_NOTIFICATION_EXISTENT)->show();

        // Check if this is the user's notification
        if($notification->user_id !== $request->user_id)
            (new JSONResult())->setError(JSONResult::ERROR_NOT_PERMITTED)->show();

        (new JSONResult())->setData([
            'notification' => $notification->formatForResponse()
        ])->show();
    }

    /**
     * Deletes a user's notification
     *
     * @param Request $request
     * @param $notificationID
     */
    public function deleteNotification(Request $request, $notificationID) {
        // Find the notification
        $notification = UserNotification::find($notificationID);

        if(!$notification)
            (new JSONResult())->setError(JSONResult::ERROR_NOTIFICATION_EXISTENT)->show();

        // Check if this is the user's notification
        if($notification->user_id !== $request->user_id)
            (new JSONResult())->setError(JSONResult::ERROR_NOT_PERMITTED)->show();

        // Delete the notification
        $notification->delete();

        (new JSONResult())->show();
    }
}
