<?php

namespace App\Listeners\Presence;

use App\Enums\UserActivityStatus;
use App\Services\Presence\PresenceTracker;
use App\Services\Presence\SocketSourceMarker;
use Illuminate\Support\Facades\Log;
use Laravel\Reverb\Application;
use Laravel\Reverb\Events\ChannelCreated;
use Laravel\Reverb\Protocols\Pusher\Channels\Channel;
use Laravel\Reverb\Protocols\Pusher\Contracts\ChannelManager;
use Laravel\Reverb\Protocols\Pusher\EventDispatcher;
use React\EventLoop\Loop;
use Throwable;

class HandleChannelCreated
{
    /**
     * The presence tracker that owns the Redis state.
     *
     * @var PresenceTracker $tracker
     */
    private PresenceTracker $tracker;

    /**
     * The source marker that identifies whether a socket originated from the API surface.
     *
     * @var SocketSourceMarker $sourceMarker
     */
    private SocketSourceMarker $sourceMarker;

    /**
     * Create the event listener.
     *
     * @param PresenceTracker    $tracker
     * @param SocketSourceMarker $sourceMarker
     */
    public function __construct(PresenceTracker $tracker, SocketSourceMarker $sourceMarker)
    {
        $this->tracker = $tracker;
        $this->sourceMarker = $sourceMarker;
    }

    /**
     * Records a presence transition when a tracked channel becomes active.
     *
     * @param ChannelCreated $event
     *
     * @return void
     */
    public function handle(ChannelCreated $event): void
    {
        $channelName = $event->channel->name();

        $userId = $this->extractUserId($channelName);

        if ($userId !== null) {
            $channel = $event->channel;
            Loop::futureTick(function () use ($userId, $channel) {
                $this->handleUser($userId, $channel);
            });
            return;
        }

        $visitorToken = $this->extractWebVisitorToken($channelName);

        if ($visitorToken !== null) {
            $this->handleGuest($visitorToken, PresenceTracker::BUCKET_WEB_GUESTS, SocketSourceMarker::SOURCE_WEB);
            return;
        }

        $appVisitorToken = $this->extractAppVisitorToken($channelName);

        if ($appVisitorToken !== null) {
            $this->handleGuest($appVisitorToken, PresenceTracker::BUCKET_API_GUESTS, SocketSourceMarker::SOURCE_API);
        }
    }

    /**
     * Records the user transition and broadcasts the status and stats deltas.
     *
     * @param int     $userId
     * @param Channel $channel
     *
     * @return void
     */
    private function handleUser(int $userId, Channel $channel): void
    {
        try {
            $source = $this->resolveSource($channel);
            $bucket = $source === SocketSourceMarker::SOURCE_API
                ? PresenceTracker::BUCKET_API_USERS
                : PresenceTracker::BUCKET_WEB_USERS;

            $wasOnline = $this->tracker->isUserGloballyOnline($userId);

            if (!$this->tracker->recordPresence($bucket, (string) $userId)) {
                return;
            }

            if (!$wasOnline) {
                $this->tracker->clearSeenRecently($userId);

                $this->publish('user-status.' . $userId, 'user.status.changed', [
                    'id' => $userId,
                    'status' => UserActivityStatus::Online,
                ]);
            }

            $this->publish('private-admin-presence-stats', 'presence.changed', [
                'source' => $source,
                'kind' => 'signed_in',
                'delta' => 1,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Presence connect failed', [
                'user_id' => $userId,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Records the guest visitor transition and broadcasts the guest stats delta.
     *
     * @param string $visitorToken
     * @param string $bucket
     * @param string $source
     *
     * @return void
     */
    private function handleGuest(string $visitorToken, string $bucket, string $source): void
    {
        try {
            if (!$this->tracker->recordPresence($bucket, $visitorToken)) {
                return;
            }

            $this->publish('private-admin-presence-stats', 'presence.changed', [
                'source' => $source,
                'kind' => 'guests',
                'delta' => 1,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Presence guest connect failed', [
                'visitor_token' => $visitorToken,
                'bucket' => $bucket,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Resolves the originating surface for the first connection on the channel.
     *
     * @param Channel $channel
     *
     * @return string
     */
    private function resolveSource(Channel $channel): string
    {
        foreach ($channel->connections() as $channelConnection) {
            $connection = $channelConnection->connection();

            return $this->sourceMarker->sourceFor($connection->id());
        }

        return SocketSourceMarker::SOURCE_WEB;
    }

    /**
     * Publishes an event through Reverb's in-process dispatcher to avoid an HTTP roundtrip into our own event loop.
     *
     * @param string $channel
     * @param string $event
     * @param array  $data
     *
     * @return void
     */
    private function publish(string $channel, string $event, array $data): void
    {
        $app = $this->reverbApplication();

        if ($app === null) {
            return;
        }

        EventDispatcher::dispatch($app, [
            'event' => $event,
            'channels' => [$channel],
            'data' => json_encode($data),
        ]);
    }

    /**
     * Returns the active Reverb application from the in-process channel manager.
     *
     * @return Application|null
     */
    private function reverbApplication(): ?Application
    {
        if (!app()->bound(ChannelManager::class)) {
            return null;
        }

        return app(ChannelManager::class)->app();
    }

    /**
     * Extracts the user identifier from a `private-users.{id}` channel name.
     *
     * @param string $channelName
     *
     * @return int|null
     */
    private function extractUserId(string $channelName): ?int
    {
        if (!preg_match('/^private-users\.(\d+)$/', $channelName, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * Extracts the visitor token from a `visitors.{token}` public channel name.
     *
     * @param string $channelName
     *
     * @return string|null
     */
    private function extractWebVisitorToken(string $channelName): ?string
    {
        if (!preg_match('/^visitors\.([A-Za-z0-9-]{8,64})$/', $channelName, $matches)) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Extracts the visitor token from an `app-visitors.{token}` public channel name.
     *
     * @param string $channelName
     *
     * @return string|null
     */
    private function extractAppVisitorToken(string $channelName): ?string
    {
        if (!preg_match('/^app-visitors\.([A-Za-z0-9-]{8,64})$/', $channelName, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
