<?php

namespace App\Http\Resources;

use App\Notifications\MALImportFinished;
use App\Notifications\NewFollower;
use App\Notifications\NewSession;
use App\Notifications\SubscriptionStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var DatabaseNotification $notification */
        $notification = $this->resource;

        return [
            'id'            => $notification->id,
            'type'          => 'notifications',
            'href'          => route('api.me.notifications.details', $notification, false),
            'attributes'    => [
                'type'          => $this->typeWithoutNamespace($notification),
                'description'   => self::getNotificationDescription($notification),
                'payload'       => $notification->data,
                'isRead'        => ($notification->read_at != null),
                'createdAt'     => $notification->created_at->format('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Returns the type of notification without the namespace.
     *
     * @param DatabaseNotification $notification
     * @return string
     */
    private function typeWithoutNamespace(DatabaseNotification $notification): string
    {
        $class_parts = explode('\\', $notification->type);
        return end($class_parts);
    }

    /**
     * Returns the body string that describes the notification.
     *
     * @param DatabaseNotification $notification
     * @return string
     */
    static function getNotificationDescription(DatabaseNotification $notification): string
    {
        switch ($notification->type) {
            case NewSession::class:
                $body = 'A new client has logged in to your account.';

                if (self::hasData($notification, 'ip_address')) {
                    $body .= ' (IP: ' . self::getData($notification, 'ip_address') . ')';
                }

                return $body;
            case NewFollower::class:
                $body = (self::hasData($notification, 'username')) ? self::getData($notification, 'username') : 'Someone';

                $body .= ' has started following you.';

                return $body;
            case MALImportFinished::class:
                $body = 'Your "MyAnimeList" import request has been processed.';

                if (self::hasData($notification, 'successful_count')) {
                    $body .= ' ' . self::getData($notification, 'successful_count') . ' Anime successfully imported.';
                }

                if (self::hasData($notification, 'failure_count')) {
                    $body .= ' ' . self::getData($notification, 'failure_count') . ' failed imports.';
                }

                return $body;
            case SubscriptionStatus::class:
                return SubscriptionStatus::getDescription(self::getData($notification, 'subscriptionStatus')) ?? '';
        }

        return 'Something went wrong... please contact an administrator.';
    }

    /**
     * Gets a data variable from the notification or return null when
     * it doesn't exist.
     *
     * @param DatabaseNotification $notification
     * @param string $key
     * @return mixed
     */
    private static function getData(DatabaseNotification $notification, string $key): mixed
    {
        return self::hasData($notification, $key) ? $notification->data[$key] : null;
    }

    /**
     * Checks whether the notification has data under the key value.
     *
     * @param DatabaseNotification $notification
     * @param string $key
     * @return bool
     */
    private static function hasData(DatabaseNotification $notification, string $key): bool
    {
        return isset($notification->data[$key]);
    }
}
