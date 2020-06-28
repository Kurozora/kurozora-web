<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static CharacterRole Protagonist()
 * @method static CharacterRole Antagonist()
 * @method static CharacterRole Deuteragonist()
 * @method static CharacterRole TertiaryCharacter()
 * @method static CharacterRole Confidante()
 * @method static CharacterRole LoveInterest()
 * @method static CharacterRole Foil()
 */
final class CharacterRole extends Enum
{
    const Protagonist       = 0;
    const Antagonist        = 1;
    const Deuteragonist     = 2;
    const TertiaryCharacter = 3;
    const Confidante        = 4;
    const LoveInterest      = 5;
    const Foil              = 6;
}
