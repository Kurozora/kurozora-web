<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use \Cog\Laravel\Love\ReactionType\Models\ReactionType as ReactionType;

/**
 * @method static ForumsVoteType Dislike()
 * @method static ForumsVoteType Like()
 */
final class ForumsVoteType extends Enum
{
    const Dislike   = -1;
    const Like      = 1;

    /**
     * Returns the next ForumsVoteType type.
     *
     * @return ForumsVoteType
     */
    public function next(): ForumsVoteType {
        switch($this) {
            case self::Dislike():
                return self::Like();
            default:
                return self::Dislike();
        }
    }

    /**
     * Returns the previous ForumsVoteType type.
     *
     * @return ForumsVoteType
     */
    public function previous(): ForumsVoteType {
        switch($this) {
            case self::Like():
                return self::Dislike();
            default:
                return self::Like();
        }
    }
}
