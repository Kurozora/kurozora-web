<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static RecapStatType BusiestWeekday()
 * @method static RecapStatType BusiestHour()
 * @method static RecapStatType LongestStreak()
 * @method static RecapStatType BiggestBinge()
 * @method static RecapStatType FirstTitle()
 * @method static RecapStatType LastTitle()
 * @method static RecapStatType TopProvider()
 * @method static RecapStatType RatingsGiven()
 * @method static RecapStatType AverageRating()
 * @method static RecapStatType ReviewsWritten()
 * @method static RecapStatType TitlesCompleted()
 * @method static RecapStatType TitlesDropped()
 * @method static RecapStatType TitlesAdded()
 * @method static RecapStatType FavoritesAdded()
 * @method static RecapStatType AchievementsEarned()
 */
final class RecapStatType extends Enum
{
    const int BusiestWeekday = 1;
    const int BusiestHour = 2;
    const int LongestStreak = 3;
    const int BiggestBinge = 4;
    const int FirstTitle = 5;
    const int LastTitle = 6;
    const int TopProvider = 7;
    const int RatingsGiven = 8;
    const int AverageRating = 9;
    const int ReviewsWritten = 10;
    const int TitlesCompleted = 11;
    const int TitlesDropped = 12;
    const int TitlesAdded = 13;
    const int FavoritesAdded = 14;
    const int AchievementsEarned = 15;
}
