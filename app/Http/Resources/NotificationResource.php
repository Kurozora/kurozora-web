<?php

namespace App\Http\Resources;

use App\Events\MALImportFinished;
use App\Notifications\NewFollower;
use App\Notifications\NewSession;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request = null)
    {
        /** @var DatabaseNotification $notification */
        $notification = $this->resource;

        return [
            'id'            => $notification->id,
            'type'          => 'notifications',
            'href'          => route('notifications.details', $notification, false),
            'attributes'    => [
                'type'          => $this->typeWithoutNamespace($notification),
                'read'          => ($notification->read_at != null),
                'data'          => $notification->data,
                'string'        => self::getNotificationString($notification),
                'created_at'    => $notification->created_at
            ]
        ];
    }

    /**
     * Returns the type of notification without the namespace.
     *
     * @param DatabaseNotification $notification
     * @return string
     */
    private function typeWithoutNamespace($notification) {
        $class_parts = explode('\\', $notification->type);
        return end($class_parts);
    }

    /**
     * Returns the body string that represents the notification.
     *
     * @param DatabaseNotification $notification
     * @return string
     */
    static function getNotificationString($notification) {
        switch($notification->type) {
            case NewSession::class: {
                $body = 'A new client has logged in to your account.';

                if(self::hasData($notification, 'ip'))
                    $body .= ' (IP: ' . self::getData($notification, 'ip') . ')';

                return $body;
            }

            case NewFollower::class: {
                $body = (self::hasData($notification, 'username')) ? self::getData($notification, 'username') : 'Someone';

                $body .= ' has started following you.';

                return $body;
            }

            case MALImportFinished::class: {
                $body = 'Your "MyAnimeList" import request has been processed.';

                if(self::hasData($notification, 'successful_count'))
                    $body .= ' ' . self::getData($notification, 'successful_count') . ' Anime successfully imported.';

                if(self::hasData($notification, 'failure_count'))
                    $body .= ' ' . self::getData($notification, 'failure_count') . ' failed imports.';

                return $body;
            }
        }

        return 'Something went wrong... please contact an administrator.';
    }

    /**
     * Gets a data variable from the notification or return null when
     * .. it doesn't exist.
     *
     * @param DatabaseNotification $notification
     * @param string $key
     * @return mixed|null
     */
    private static function getData($notification, $key) {
        return isset($notification->data[$key]) ? $notification->data[$key] : null;
    }

    /**
     * Checks whether the notification has data under the key value.
     *
     * @param DatabaseNotification $notification
     * @param string $key
     * @return bool
     */
    private static function hasData($notification, $key) {
        return isset($notification->data[$key]);
    }
}
