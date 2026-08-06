<?php

namespace App\Listeners\Presence;

use App\Enums\UserActivityStatus;
use App\Services\Presence\PresenceTracker;
use Illuminate\Support\Facades\Log;
use Laravel\Reverb\Events\MessageReceived;
use Throwable;

class PushUserStatusSnapshotOnSubscribe
{
    /**
     * The presence tracker that owns the Redis state.
     *
     * @var PresenceTracker $tracker
     */
    private PresenceTracker $tracker;

    /**
     * Create the event listener.
     *
     * @param PresenceTracker $tracker
     */
    public function __construct(PresenceTracker $tracker)
    {
        $this->tracker = $tracker;
    }

    /**
     * Sends the current activity status directly to a connection that just subscribed to a `user-status.{id}` channel.
     *
     * @param MessageReceived $event
     *
     * @return void
     */
    public function handle(MessageReceived $event): void
    {
        try {
            $payload = json_decode($event->message, true);

            if (!is_array($payload)) {
                return;
            }

            if (($payload['event'] ?? null) !== 'pusher:subscribe') {
                return;
            }

            $channel = $payload['data']['channel'] ?? null;

            if (!is_string($channel)) {
                return;
            }

            if (!preg_match('/^user-status\.(\d+)$/', $channel, $matches)) {
                return;
            }

            $userId = (int) $matches[1];
            $status = match (true) {
                $this->tracker->isUserGloballyOnline($userId) => UserActivityStatus::Online,
                $this->tracker->isSeenRecently($userId) => UserActivityStatus::SeenRecently,
                default => UserActivityStatus::Offline,
            };

            $event->connection->send(json_encode([
                'event' => 'user.status.changed',
                'channel' => $channel,
                'data' => json_encode([
                    'id' => $userId,
                    'status' => $status,
                ]),
            ]));
        } catch (Throwable $exception) {
            Log::warning('Presence subscribe snapshot failed', [
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
