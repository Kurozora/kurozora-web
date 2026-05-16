<?php

namespace App\Notifications\Concerns;

use Illuminate\Support\Str;

trait BroadcastsAsNotification
{
    /**
     * The Pusher-protocol event name advertised on the wire.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    /**
     * The notification kind included in the broadcast payload as `data.type`.
     *
     * @return string
     */
    public function broadcastType(): string
    {
        return Str::kebab(class_basename(static::class));
    }
}
