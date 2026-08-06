<?php

namespace App\Services\Presence;

use Illuminate\Support\Facades\Redis;

class SocketSourceMarker
{
    /**
     * The TTL on the source marker, in seconds.
     */
    private const int TTL_SECONDS = 60;

    /**
     * The source identifier for connections originating from the API surface.
     */
    public const string SOURCE_API = 'api';

    /**
     * The source identifier for connections originating from the web surface.
     */
    public const string SOURCE_WEB = 'web';

    /**
     * Marks the socket as originating from the API.
     *
     * @param string $socketID
     *
     * @return void
     */
    public function markApi(string $socketID): void
    {
        if ($socketID === '') {
            return;
        }

        Redis::setex($this->key($socketID), self::TTL_SECONDS, self::SOURCE_API);
    }

    /**
     * Returns the source of the socket, defaulting to the web surface when no marker is present.
     *
     * @param string $socketID
     *
     * @return string
     */
    public function sourceFor(string $socketID): string
    {
        if ($socketID === '') {
            return self::SOURCE_WEB;
        }

        $stored = Redis::get($this->key($socketID));

        return $stored === self::SOURCE_API ? self::SOURCE_API : self::SOURCE_WEB;
    }

    /**
     * Returns the Redis key holding the source marker for the socket.
     *
     * @param string $socketID
     *
     * @return string
     */
    private function key(string $socketID): string
    {
        return 'presence:socket-source:' . $socketID;
    }
}
