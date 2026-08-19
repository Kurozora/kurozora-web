<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static FavoriteSentiment NotEnough()
 * @method static FavoriteSentiment GuiltyPleasure()
 * @method static FavoriteSentiment FanFavorite()
 * @method static FavoriteSentiment WidelyLiked()
 * @method static FavoriteSentiment NotWidelyLiked()
 * @method static FavoriteSentiment RarelyLiked()
 */
final class FavoriteSentiment extends Enum
{
    const int NotEnough = 0;
    const int GuiltyPleasure = 1;
    const int FanFavorite = 2;
    const int WidelyLiked = 3;
    const int NotWidelyLiked = 4;
    const int RarelyLiked = 5;

    protected static function getLocalizedDescription(mixed $value): ?string
    {
        return match ($value) {
            self::NotEnough => __('Not enough favorites'),
            self::GuiltyPleasure => __('Guilty Pleasure'),
            self::FanFavorite => __('Fan Favorite'),
            self::WidelyLiked => __('Widely Liked'),
            self::NotWidelyLiked => __('Not Widely Liked'),
            self::RarelyLiked => __('Rarely Liked'),
        };
    }
}
