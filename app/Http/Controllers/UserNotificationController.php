<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
use App\UserNotification;

class UserNotificationController extends Controller
{
    /**
     * Retrieves details for a specific notification
     *
     * @param UserNotification $notification
     * @throws \ReflectionException
     */
    public function getNotification(UserNotification $notification) {
        (new JSONResult())->setData([
            'notification' => $notification->formatForResponse()
        ])->show();
    }

    /**
     * Deletes a user's notification
     *
     * @param UserNotification $notification
     * @throws \Exception
     */
    public function delete(UserNotification $notification) {
        // Delete the notification
        $notification->delete();

        (new JSONResult())->show();
    }
}
