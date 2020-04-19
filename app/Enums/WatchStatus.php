<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * The set of available watch status types.
 *
 * @method static static NotWatched()
 * @method static static Disabled()
 * @method static static Watched()
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
	 * Instantiates a WatchStatus instance from the given boolean value.
	 *
	 * @param bool $bool The boolean value used to instantiate a WatchStatus instance.
	 *
	 * @return \App\Enums\WatchStatus
	 */
	static function init($bool): self {
		return $bool ? self::WATCHED() : self::NOT_WATCHED();
	}
}
