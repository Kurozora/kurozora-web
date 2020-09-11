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
        switch($this) {
            case self::UnHeart():
                return self::Heart();
            default:
                return self::UnHeart();
        }
    }

    /**
     * Returns the previous FeedVoteType type.
     *
     * @return FeedVoteType
     */
    public function previous(): FeedVoteType {
        switch($this) {
            case self::Heart():
                return self::UnHeart();
            default:
                return self::Heart();
        }
    }
}
