<?php

namespace App\Http\Controllers;

use App\Helpers\JSONResult;
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
}
