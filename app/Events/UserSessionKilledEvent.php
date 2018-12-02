<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/*
 * This event is called when a user's session is destroyed/killed
 */
class UserSessionKilledEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userID;
    public $sessionID;
    public $reason;
    public $killerID;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($userID, $sessionID, $reason, $killerID)
    {
        $this->userID = $userID;
        $this->sessionID = $sessionID;
        $this->reason = $reason;
        $this->killerID = $killerID;
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'session.killed';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'user_id'       => (int) $this->userID,
            'session_id'    => (int) $this->sessionID,
            'reason'        => $this->reason,
            'killer_id'    => (int) $this->killerID,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return ['private-user.' . $this->userID];
    }
}
