<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * The set of available watch status types.
 *
 * @method static static NOT_WATCHED()
 * @method static static DISABLED()
 * @method static static WATCHED()
 */
final class WatchStatus extends Enum
{
	/// The episode is not watched.
	const NOT_WATCHED    = -1;

	/// The episode can't be watched or unwatched.
	const DISABLED      = 0;

	/// The episode is watched.
	const WATCHED       = 1;

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
