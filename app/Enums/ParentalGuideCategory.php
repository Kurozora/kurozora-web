<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static ParentalGuideCategory SexAndNudity()
 * @method static ParentalGuideCategory ViolenceAndGore()
 * @method static ParentalGuideCategory Profanity()
 * @method static ParentalGuideCategory AlcoholDrugsAndSmoking()
 * @method static ParentalGuideCategory FrighteningAndIntenseScenes()
 */
final class ParentalGuideCategory extends Enum
{
    const int SexAndNudity = 1;
    const int ViolenceAndGore = 2;
    const int Profanity = 3;
    const int AlcoholDrugsAndSmoking = 4;
    const int FrighteningAndIntenseScenes = 5;

    protected static function getLocalizedDescription(mixed $value): ?string
    {
        return match ($value) {
            self::SexAndNudity => __('Sex & Nudity'),
            self::ViolenceAndGore => __('Violence & Gore'),
            self::Profanity => __('Profanity'),
            self::AlcoholDrugsAndSmoking => __('Alcohol, Drugs & Smoking'),
            self::FrighteningAndIntenseScenes => __('Frightening & Intense Scenes'),
        };
    }
}
