<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use \Cog\Laravel\Love\ReactionType\Models\ReactionType as ReactionType;

/**
 * @method static VoteType Dislike()
 * @method static VoteType Like()
 */
final class VoteType extends Enum
{
    const Dislike   = -1;
    const Like      = 1;

    /**
     * Returns the next VoteType type.
     *
     * @return VoteType
     */
    public function next(): VoteType {
        switch($this) {
            case self::Dislike():
                return self::Like();
                break;
            default:
                return self::Dislike();
                break;
        }
    }

    /**
     * Returns the previous VoteType type.
     *
     * @return VoteType
     */
    public function previous(): VoteType {
        switch($this) {
            case self::Like():
                return self::Dislike();
                break;
            default:
                return self::Like();
                break;
        }
    }
}
