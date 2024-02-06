<?php declare(strict_types=1);

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
    const int NotWatched = -1;

    // The episode can't be watched or unwatched.
    const int Disabled  = 0;

    // The episode is watched.
    const int Watched = 1;

    /**
     * The bool value of one of the enum members.
     *
     * @var ?bool
     */
    public ?bool $boolValue;

    /**
     * Construct an Enum instance.
     *
     * @param mixed $enumValue
     * @return void
     * @throws InvalidEnumMemberException
     */
    public function __construct(mixed $enumValue)
    {
        parent::__construct($enumValue);

        $this->boolValue = WatchStatus::getBoolValue($enumValue);
    }

    /**
     * Instantiates a WatchStatus instance from the given boolean value.
     *
     * @param bool $bool The boolean value used to instantiate a WatchStatus instance.
     * @return WatchStatus
     */
    public static function fromBool(bool $bool): self
    {
        return $bool ? self::Watched() : self::NotWatched();
    }

    /**
     * Returns the bool or null value of one of the enum values.
     *
     * @param int $enumValue
     * @return ?bool
     */
    public static function getBoolValue(int $enumValue): ?bool
    {
        return match ($enumValue) {
            self::Watched => true,
            self::NotWatched => false,
            default => null,
        };
    }
}
