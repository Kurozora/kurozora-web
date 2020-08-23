<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;

/**
 * The set of available watch status types.
 *
 * @method static WatchStatus NotWatched()
 * @method static WatchStatus Disabled()
 * @method static WatchStatus Watched()
 */
final class WatchStatus extends Enum
{
    // The episode is not watched.
    const NotWatched = -1;

    // The episode can't be watched or unwatched.
    const Disabled  = 0;

    // The episode is watched.
    const Watched = 1;

    /**
     * The bool value of one of the enum members.
     *
     * @var mixed
     */
    public $boolValue;

    /**
     * Construct an Enum instance.
     *
     * @param mixed $enumValue
     * @return void
     * @throws InvalidEnumMemberException
     */
    public function __construct($enumValue)
    {
        parent::__construct($enumValue);
        $this->boolValue = static::getBoolValue($enumValue);
    }

    /**
     * Instantiates a WatchStatus instance from the given boolean value.
     *
     * @param bool $bool The boolean value used to instantiate a WatchStatus instance.
     * @return WatchStatus
     */
    public static function fromBool(bool $bool = null): self
    {
        if ($bool == null)
            return self::NotWatched();

        return $bool ? self::Watched() : self::NotWatched();
    }

    /**
     * Returns the bool or null value of one of the enum values.
     *
     * @param mixed $enumValue
     * @return bool|null
     */
    public static function getBoolValue($enumValue)
    {
        if ($enumValue === self::Disabled)
            return null;

        return $enumValue === self::Watched;
    }
}
