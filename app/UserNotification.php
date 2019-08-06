<?php

namespace App;

use ReflectionClass;

class UserNotification extends KModel
{
    // Types of notification
    const TYPE_UNKNOWN      = 0;
    const TYPE_NEW_FOLLOWER = 1;
    const TYPE_NEW_SESSION  = 2;

    // Table name
    const TABLE_NAME = 'user_notifications';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the user the notification belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns the string/text for this notification
     *
     * @return string
     */
    public function getString() {
        switch($this->type) {
            // Someone started following the user
            case self::TYPE_NEW_FOLLOWER: {
                $followerName = $this->getDataVariable('follower_name');

                return
                    (($followerName == null) ? 'An unknown user' : $followerName) .
                    ' started following you';

                break;
            }
            // A new client/session was made for the user
            case self::TYPE_NEW_SESSION: {
                $sessionIP = $this->getDataVariable('ip');

                return
                    'A new client has logged in to your account.' .
                    (($sessionIP != null) ? ' (IP: ' . $sessionIP . ')' : null);

                break;
            }
            // Unknown type of notification
            default: {
                return 'Error retrieving this notification';
            }
        }
    }

    /**
     * Returns the type of the notification as a string
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getTypeString() {
        $reflection = new ReflectionClass(get_class($this));

        $constants_array = $reflection->getConstants();

        foreach ($constants_array as $constant_key => $constant_value) {
            if($this->type == $constant_value)
                return $constant_key;
        }

        return 'TYPE_UNKNOWN';
    }

    /**
     * Returns the data for the notification
     *
     * @return array
     */
    public function getData() {
        $decoded =  json_decode($this->data);

        if($decoded == null)
            return [];

        return (array) $decoded;
    }

    /**
     * Returns the value of a variable from the data field of this notification
     *
     * @param $varName
     * @return mixed|null
     */
    public function getDataVariable($varName) {
        $data = $this->getData();

        if(isset($data[$varName]))
            return $data[$varName];

        return null;
    }
}
