<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserStateChanged implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable,
        InteractsWithSockets,
        SerializesModels;

    /**
     * The identifier of the user whose state version advanced.
     *
     * @var int $userID
     */
    public int $userID;

    /**
     * The new state version after the bump.
     *
     * @var int $stateVersion
     */
    public int $stateVersion;

    /**
     * Create a new event instance.
     *
     * @param int $userID
     * @param int $stateVersion
     */
    public function __construct(int $userID, int $stateVersion)
    {
        $this->userID = $userID;
        $this->stateVersion = $stateVersion;
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
        return 'user.state.changed';
    }

    /**
     * The payload sent to subscribers.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'stateVersion' => $this->stateVersion,
        ];
    }
}
