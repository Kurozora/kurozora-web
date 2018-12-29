<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

class UserNotification extends Model
{
    // Types of notification
    const TYPE_UNKNOWN      = 0;
    const TYPE_NEW_FOLLOWER = 1;
    const TYPE_NEW_SESSION  = 2;

    // Table name
    const TABLE_NAME = 'user_notification';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = ['user_id', 'type', 'data'];

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

    /**
     * Formats the notification for a response
     *
     * @return array
     * @throws \ReflectionException
     */
    public function formatForResponse() {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'type'          => $this->getTypeString(),
            'read'          => (bool) $this->read,
            'data'          => $this->getData(),
            'string'        => $this->getString(),
            'creation_date' => (string) $this->created_at
        ];
    }
}
