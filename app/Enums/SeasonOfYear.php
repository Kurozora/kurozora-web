<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;
use BladeUI\Icons\Svg;
use Carbon\Carbon;

/**
 * @method static SeasonOfYear Winter()
 * @method static SeasonOfYear Spring()
 * @method static SeasonOfYear Summer()
 * @method static SeasonOfYear Fall()
 */
final class SeasonOfYear extends Enum
{
    const int Winter = 0;
    const int Spring = 1;
    const int Summer = 2;
    const int Fall   = 3;

    /**
     * The symbol of the season.
     *
     * @return Svg
     */
    public function symbol(): Svg
    {
        return match ($this->value) {
            self::Spring => svg('leaf_fill', 'fill-current', ['width' => '20']),
            self::Summer => svg('sun_max_fill', 'fill-current', ['width' => '20']),
            self::Fall => svg('wind', 'fill-current', ['width' => '20']),
            default => svg('snowflake', 'fill-current', ['width' => '20'])
        };
    }

    /**
     * Returns the next SeasonOfYear type.
     *
     * @param int $steps
     * @return SeasonOfYear
     */
    public function next(int $steps = 1): SeasonOfYear
    {
        $seasonOfYear = $this;

        foreach (range(0, $steps - 1) as $ignored) {
            $seasonOfYear = match ($seasonOfYear->value) {
                self::Winter => self::Spring(),
                self::Spring => self::Summer(),
                self::Summer => self::Fall(),
                default => self::Winter(),
            };
        }

        return $seasonOfYear;
    }

    /**
     * Returns the previous SeasonOfYear type.
     *
     * @param int $steps
     * @return SeasonOfYear
     */
    public function previous(int $steps = 1): SeasonOfYear
    {
        $seasonOfYear = $this;

        foreach (range(0, $steps - 1) as $ignored) {
            $seasonOfYear = match ($seasonOfYear->value) {
                self::Fall => self::Summer(),
                self::Summer => self::Spring(),
                self::Spring => self::Winter(),
                default => self::Fall(),
            };
        }

        return $seasonOfYear;
    }

    /**
     * Returns the start date of the season.
     *
     * @return Carbon
     */
    public function startDate(): Carbon
    {
        return match ($this->value) {
            self::Spring => Carbon::create(0, 4),
            self::Summer => Carbon::create(0, 7),
            self::Fall => Carbon::create(0, 10),
            default => Carbon::create(),
        };
    }

    /**
     * Returns the end date of the season.
     *
     * @return Carbon
     */
    public function endDate(): Carbon
    {
        return match ($this->value) {
            self::Spring => Carbon::create(0, 6, 30),
            self::Summer => Carbon::create(0, 9, 30),
            self::Fall => Carbon::create(0, 12, 31),
            default => Carbon::create(0, 3, 31),
        };
    }
}
