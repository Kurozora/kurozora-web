<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static FeedVoteType UnHeart()
 * @method static FeedVoteType Heart()
 */
final class FeedVoteType extends Enum
{
    const UnHeart   =   0;
    const Heart     =   1;

    /**
     * Returns the next FeedVoteType type.
     *
     * @return FeedVoteType
     */
    public function next(): FeedVoteType {
        return match ($this) {
            self::UnHeart() => self::Heart(),
            default => self::UnHeart(),
        };
    }

    /**
     * Returns the previous FeedVoteType type.
     *
     * @return FeedVoteType
     */
    public function previous(): FeedVoteType {
        return match ($this) {
            self::Heart() => self::UnHeart(),
            default => self::Heart(),
        };
    }
}
