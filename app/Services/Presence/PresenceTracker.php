<?php

namespace App\Services\Presence;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class PresenceTracker
{
    /**
     * The bucket name for signed-in users connected from the web.
     */
    public const string BUCKET_WEB_USERS = 'web:users';

    /**
     * The bucket name for signed-in users connected from the API.
     */
    public const string BUCKET_API_USERS = 'api:users';

    /**
     * The bucket name for guest browser contexts connected from the web.
     */
    public const string BUCKET_WEB_GUESTS = 'web:guests';

    /**
     * The bucket name for guest app contexts connected from the API.
     */
    public const string BUCKET_API_GUESTS = 'api:guests';

    /**
     * The user presence buckets used by global online status computation.
     */
    private const array USER_BUCKETS = [
        self::BUCKET_WEB_USERS,
        self::BUCKET_API_USERS,
    ];

    /**
     * The TTL on the pending-disconnect marker, in seconds.
     */
    private const int PENDING_TTL_SECONDS = 10;

    /**
     * The window during which a user with no active sockets is still considered "seen recently", in seconds.
     */
    public const int SEEN_RECENTLY_WINDOW_SECONDS = 600;

    /**
     * The TTL on the seen-recently pending marker, in seconds. Sized slightly larger than the window so the finalizer can run.
     */
    private const int SEEN_RECENTLY_PENDING_TTL_SECONDS = self::SEEN_RECENTLY_WINDOW_SECONDS + 30;

    /**
     * Records that the given identifier has at least one active socket in the bucket.
     *
     * @param string $bucket
     * @param string $id
     *
     * @return bool
     */
    public function recordPresence(string $bucket, string $id): bool
    {
        Redis::del($this->pendingKey($bucket, $id));

        return (int) Redis::sadd($this->bucketKey($bucket), $id) === 1;
    }

    /**
     * Marks the identifier as awaiting disconnect in the bucket and returns the token the finalizer must match.
     *
     * @param string $bucket
     * @param string $id
     *
     * @return string
     */
    public function markDisconnectPending(string $bucket, string $id): string
    {
        $token = (string) Str::uuid();

        Redis::setex($this->pendingKey($bucket, $id), self::PENDING_TTL_SECONDS, $token);

        return $token;
    }

    /**
     * Finalizes the disconnect when the grace window expires without a reconnection.
     *
     * @param string $bucket
     * @param string $id
     * @param string $token
     *
     * @return bool
     */
    public function finaliseDisconnect(string $bucket, string $id, string $token): bool
    {
        $stored = Redis::get($this->pendingKey($bucket, $id));

        if ($stored !== $token) {
            return false;
        }

        Redis::del($this->pendingKey($bucket, $id));
        Redis::srem($this->bucketKey($bucket), $id);

        return true;
    }

    /**
     * Returns the count of identifiers currently present in the bucket.
     *
     * @param string $bucket
     *
     * @return int
     */
    public function count(string $bucket): int
    {
        return (int) Redis::scard($this->bucketKey($bucket));
    }

    /**
     * Returns whether the user is currently online via any source.
     *
     * @param int $userID
     *
     * @return bool
     */
    public function isUserGloballyOnline(int $userID): bool
    {
        return array_any(self::USER_BUCKETS, fn($bucket) => Redis::sismember($this->bucketKey($bucket), (string) $userID));
    }

    /**
     * Returns the user bucket currently containing the user.
     *
     * @param int $userID
     *
     * @return string|null
     */
    public function userBucketFor(int $userID): ?string
    {
        return array_find(self::USER_BUCKETS, fn($bucket) => Redis::sismember($this->bucketKey($bucket), (string) $userID));
    }

    /**
     * Records the timestamp at which the user fully disconnected and arms the pending seen-recently finalizer.
     *
     * @param int $userID
     *
     * @return string
     */
    public function markSeenRecentlyPending(int $userID): string
    {
        $token = (string) Str::uuid();

        Redis::setex($this->lastSeenKey($userID), self::SEEN_RECENTLY_PENDING_TTL_SECONDS, (string) time());
        Redis::setex($this->seenRecentlyPendingKey($userID), self::SEEN_RECENTLY_PENDING_TTL_SECONDS, $token);

        return $token;
    }

    /**
     * Consumes the seen-recently pending marker, returning whether the token matches the current pending state.
     *
     * @param int    $userID
     * @param string $token
     *
     * @return bool
     */
    public function consumeSeenRecentlyPending(int $userID, string $token): bool
    {
        $stored = Redis::get($this->seenRecentlyPendingKey($userID));

        if ($stored !== $token) {
            return false;
        }

        Redis::del($this->seenRecentlyPendingKey($userID));

        return true;
    }

    /**
     * Clears the seen-recently state when the user returns online via any source.
     *
     * @param int $userID
     *
     * @return void
     */
    public function clearSeenRecently(int $userID): void
    {
        Redis::del($this->seenRecentlyPendingKey($userID));
        Redis::del($this->lastSeenKey($userID));
    }

    /**
     * Returns whether the user has fully disconnected within the seen-recently window.
     *
     * @param int $userID
     *
     * @return bool
     */
    public function isSeenRecently(int $userID): bool
    {
        $stored = Redis::get($this->lastSeenKey($userID));

        if ($stored === null) {
            return false;
        }

        return (int) $stored >= time() - self::SEEN_RECENTLY_WINDOW_SECONDS;
    }

    /**
     * Returns the Redis key holding the membership set for the bucket.
     *
     * @param string $bucket
     *
     * @return string
     */
    private function bucketKey(string $bucket): string
    {
        return 'presence:' . $bucket;
    }

    /**
     * Returns the Redis key holding the pending-disconnect marker for the identifier in the bucket.
     *
     * @param string $bucket
     * @param string $id
     *
     * @return string
     */
    private function pendingKey(string $bucket, string $id): string
    {
        return 'presence:' . $bucket . ':' . $id . ':pending';
    }

    /**
     * Returns the Redis key holding the user's last-fully-disconnected timestamp.
     *
     * @param int $userID
     *
     * @return string
     */
    private function lastSeenKey(int $userID): string
    {
        return 'presence:last-seen:' . $userID;
    }

    /**
     * Returns the Redis key holding the seen-recently pending-finalizer token.
     *
     * @param int $userID
     *
     * @return string
     */
    private function seenRecentlyPendingKey(int $userID): string
    {
        return 'presence:seen-recently-pending:' . $userID;
    }
}
