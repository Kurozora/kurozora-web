<?php

namespace App\Listeners\Presence;

use App\Enums\UserActivityStatus;
use App\Services\Presence\PresenceTracker;
use App\Services\Presence\SocketSourceMarker;
use Illuminate\Support\Facades\Log;
use Laravel\Reverb\Application;
use Laravel\Reverb\Events\ChannelRemoved;
use Laravel\Reverb\Protocols\Pusher\Contracts\ChannelManager;
use Laravel\Reverb\Protocols\Pusher\EventDispatcher;
use React\EventLoop\Loop;
use Throwable;

class HandleChannelRemoved
{
    /**
     * The grace window before the offline transition fires, in seconds.
     */
    private const float GRACE_SECONDS = 5.0;

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
     * Schedules a debounced offline transition when a tracked channel becomes empty.
     *
     * @param ChannelRemoved $event
     *
     * @return void
     */
    public function handle(ChannelRemoved $event): void
    {
        $channelName = $event->channel->name();

        $userId = $this->extractUserId($channelName);

        if ($userId !== null) {
            $this->scheduleUserDisconnect($userId);
            return;
        }

        $visitorToken = $this->extractWebVisitorToken($channelName);

        if ($visitorToken !== null) {
            $this->scheduleGuestDisconnect($visitorToken, PresenceTracker::BUCKET_WEB_GUESTS, SocketSourceMarker::SOURCE_WEB);
            return;
        }

        $appVisitorToken = $this->extractAppVisitorToken($channelName);

        if ($appVisitorToken !== null) {
            $this->scheduleGuestDisconnect($appVisitorToken, PresenceTracker::BUCKET_API_GUESTS, SocketSourceMarker::SOURCE_API);
        }
    }

    /**
     * Schedules the debounced offline transition for a signed-in user.
     *
     * @param int $userId
     *
     * @return void
     */
    private function scheduleUserDisconnect(int $userId): void
    {
        try {
            $bucket = $this->tracker->userBucketFor($userId);

            if ($bucket === null) {
                return;
            }

            $source = $bucket === PresenceTracker::BUCKET_API_USERS
                ? SocketSourceMarker::SOURCE_API
                : SocketSourceMarker::SOURCE_WEB;

            $token = $this->tracker->markDisconnectPending($bucket, (string) $userId);
            $tracker = $this->tracker;
            $application = $this->reverbApplication();

            Loop::get()->addTimer(self::GRACE_SECONDS, function () use ($userId, $token, $bucket, $source, $tracker, $application) {
                if (!$tracker->finaliseDisconnect($bucket, (string) $userId, $token)) {
                    return;
                }

                if ($application === null) {
                    return;
                }

                if (!$tracker->isUserGloballyOnline($userId)) {
                    $seenRecentlyToken = $tracker->markSeenRecentlyPending($userId);

                    EventDispatcher::dispatch($application, [
                        'event' => 'user.status.changed',
                        'channels' => ['user-status.' . $userId],
                        'data' => json_encode([
                            'id' => $userId,
                            'status' => UserActivityStatus::SeenRecently
                        ]),
                    ]);

                    Loop::get()->addTimer(PresenceTracker::SEEN_RECENTLY_WINDOW_SECONDS, function () use ($userId, $seenRecentlyToken, $tracker, $application) {
                        if (!$tracker->consumeSeenRecentlyPending($userId, $seenRecentlyToken)) {
                            return;
                        }

                        if ($tracker->isUserGloballyOnline($userId)) {
                            return;
                        }

                        EventDispatcher::dispatch($application, [
                            'event' => 'user.status.changed',
                            'channels' => ['user-status.' . $userId],
                            'data' => json_encode([
                                'id' => $userId,
                                'status' => UserActivityStatus::Offline,
                            ]),
                        ]);
                    });
                }

                EventDispatcher::dispatch($application, [
                    'event' => 'presence.changed',
                    'channels' => ['private-admin-presence-stats'],
                    'data' => json_encode([
                        'source' => $source,
                        'kind' => 'signed_in',
                        'delta' => -1,
                    ]),
                ]);
            });
        } catch (Throwable $exception) {
            Log::warning('Presence disconnect schedule failed', [
                'user_id' => $userId,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Schedules the debounced offline transition for a guest browser or app context.
     *
     * @param string $visitorToken
     * @param string $bucket
     * @param string $source
     *
     * @return void
     */
    private function scheduleGuestDisconnect(string $visitorToken, string $bucket, string $source): void
    {
        try {
            $token = $this->tracker->markDisconnectPending($bucket, $visitorToken);
            $tracker = $this->tracker;
            $application = $this->reverbApplication();

            Loop::get()->addTimer(self::GRACE_SECONDS, function () use ($visitorToken, $token, $bucket, $source, $tracker, $application) {
                if (!$tracker->finaliseDisconnect($bucket, $visitorToken, $token)) {
                    return;
                }

                if ($application === null) {
                    return;
                }

                EventDispatcher::dispatch($application, [
                    'event' => 'presence.changed',
                    'channels' => ['private-admin-presence-stats'],
                    'data' => json_encode([
                        'source' => $source,
                        'kind' => 'guests',
                        'delta' => -1,
                    ]),
                ]);
            });
        } catch (Throwable $exception) {
            Log::warning('Presence guest disconnect schedule failed', [
                'visitor_token' => $visitorToken,
                'bucket' => $bucket,
                'exception' => $exception->getMessage(),
            ]);
        }
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
