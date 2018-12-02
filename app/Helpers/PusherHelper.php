<?php

use Pusher\Pusher;
use Pusher\PusherException;

class PusherHelper {
    protected $pusherHandle = null;

    /**
     * PusherHelper constructor.
     * Initiates Pusher connection handle
     *
     * @return bool
     */
    public function __construct() {
        try {
            // Create the pusher connection
            $this->pusherHandle = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options')
            );

            return true;
        }
        catch(PusherException $e) {
            return false;
        }
    }

    /**
     * Returns information about a channel
     *
     * @param $channel
     * @return null|object
     */
    public function getChannelInfo($channel) {
        if($this->pusherHandle == null)
            return null;

        try {
            return $this->pusherHandle->get_channel_info($channel);
        }
        catch(PusherException $e) {
            return null;
        }
    }

    /**
     * Attempts to authenticate a socket ID into a private channel
     *
     * @param $channel
     * @param $socketID
     * @return null|string
     */
    public function socketAuth($channel, $socketID) {
        if($this->pusherHandle == null)
            return null;

        try {
            return $this->pusherHandle->socket_auth($channel, $socketID);
        }
        catch(PusherException $p) {
            return null;
        }
    }
}