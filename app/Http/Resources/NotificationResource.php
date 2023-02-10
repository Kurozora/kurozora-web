<?php

namespace App\Http\Resources;

use App\Notifications\LibraryImportFinished;
use App\Notifications\NewFeedMessageReply;
use App\Notifications\NewFeedMessageReShare;
use App\Notifications\NewFollower;
use App\Notifications\NewSession;
use App\Notifications\SubscriptionStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;

class NotificationResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var DatabaseNotification $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'            => $this->resource->id,
            'uuid'          => (string) $this->resource->id,
            'type'          => 'notifications',
            'href'          => route('api.me.notifications.details', $this->resource, false),
            'attributes'    => [
                'type'          => class_basename($this->resource->type),
                'description'   => self::getNotificationDescription($this->resource),
                'payload'       => $this->resource->data,
                'isRead'        => ($this->resource->read_at != null),
                'createdAt'     => $this->resource->created_at->timestamp,
            ]
        ];
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
            // Session notifications
            case NewSession::class:
                $body = 'A new client has logged in to your account.';

                if (self::hasData($notification, 'ip_address')) {
                    $body .= ' (IP: ' . self::getData($notification, 'ip_address') . ')';
                }

                return $body;
            // Follower notifications
            case NewFollower::class:
                $body = (self::hasData($notification, 'username')) ? self::getData($notification, 'username') : 'Someone';
                $body .= ' has started following you.';
                return $body;
            // Feed notifications
            case NewFeedMessageReply::class:
                $body = (self::hasData($notification, 'username')) ? self::getData($notification, 'username') : 'Someone';
                $body .= ' Replied to Your Message.';
                return $body;
            case NewFeedMessageReShare::class:
                $body = (self::hasData($notification, 'username')) ? self::getData($notification, 'username') : 'Someone';
                $body .= ' ReShared Your Message';
                return $body;
            // Anime import notifications
            case LibraryImportFinished::class:
                $serviceName = self::getData($notification, 'service');
                $body = 'Your "' . $serviceName . '" anime import request has been processed.';

                if (self::hasData($notification, 'successful_count')) {
                    $body .= ' ' . self::getData($notification, 'successful_count') . ' Anime successfully imported.';
                }

                if (self::hasData($notification, 'failure_count')) {
                    $body .= ' ' . self::getData($notification, 'failure_count') . ' failed imports.';
                }

                return $body;
            // Subscription notifications
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
