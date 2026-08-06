<?php

namespace App\Events\Notifications;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationDeleted implements ShouldBroadcast
{
    use Dispatchable,
        InteractsWithSockets,
        SerializesModels;

    /**
     * The identifier of the user whose notifications changed.
     *
     * @var int $userID
     */
    public int $userID;

    /**
     * The notification ids removed, or the string `all` for every row.
     *
     * @var array|string $ids
     */
    public array|string $ids;

    /**
     * Create a new event instance.
     *
     * @param int          $userID
     * @param array|string $ids
     */
    public function __construct(int $userID, array|string $ids)
    {
        $this->userID = $userID;
        $this->ids = $ids;
    }

    /**
     * The private channel this event is broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('users.' . $this->userID);
    }

    /**
     * The Pusher-protocol event name advertised on the wire.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'notification.deleted';
    }

    /**
     * The payload sent to subscribers.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'ids' => $this->ids,
        ];
    }
}
