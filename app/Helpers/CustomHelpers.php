<?php

use App\Enums\SeasonOfYear;
use App\Helpers\Settings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

// Create a deeplink iOS URL
if (!function_exists('ios_app_url')) {
    function ios_app_url($path): string
    {
        return config('app.ios_app_protocol') . $path;
    }
}

if (!function_exists('settings')) {
    /**
     * The settings of the user.
     *
     * Provided both arguments to set a value. Provide only one to get a value.
     * If none is provided, then the Settings object is returned.
     *
     * @param ?string $key
     * @param mixed $value
     * @param bool $setEmptyValue
     * @return mixed
     */
    function settings(?string $key = null, mixed $value = null, bool $setEmptyValue = false): mixed
    {
        /** @var Settings $settings */
        $settings = app(Settings::class);

        return $settings?->settings($key, $value, $setEmptyValue);
    }
}

if (!function_exists('number_shorten')) {
    /**
     * Shorten the number and append a unit.
     *
     * Units: Thousand (K), Million (M), Billion (B), Trillion (T), Quadrillion (Qa), Quintillion (Qi), Sextillion (Sx), Septillion (Sp), Octillion (Oc), Nonillion (No), Decillion (Dc), Undecillion (Ud), Duodecillion (Dd), Tredecillion (Td), Quattuordecillion (Qat), Quinquadecillion (Qid), Sexdecillion (Sxd), Septendecillion (Spd), Octodecillion (Ocd), Novendecillion (Nod), Vigintillion(Vg), Vunvigintillion (Uvg)
     *
     * @param int|float $number
     * @param int $precision
     * @param bool $abbreviated
     * @return string
     */
    function number_shorten(int|float $number, int $precision = 3, bool $abbreviated = false): string
    {
        if ($abbreviated) {
            $suffixes = ['', 'K', 'M', 'B', 'T', 'Qa', 'Qi', 'Sx', 'Sp', 'Oc', 'No', 'Dc', 'Ud', 'Dd', 'Td', 'Qat', 'Qid', 'Sxd', 'Spd', 'Ocd', 'Nod', 'Vg', 'Uvg'];
        } else {
            $suffixes = ['', 'Thousand', 'Million', 'Billion', 'Trillion', 'Quadrillion', 'Quintillion', 'Sextillion', 'Septillion', 'Octillion', 'Nonillion ', 'Decillion', 'Undecillion', 'Duodecillion', 'Tredecillion', 'Quattuordecillion', 'Quinquadecillion', 'Sexdecillion', 'Septendecillion', 'Octodecillion', 'Novendecillion', 'Vigintillion', 'Vunvigintillion'];
        }
        $index = (int) log(abs($number), 1000);
        $index = max(0, min(count($suffixes) - 1, $index)); // Clamps to a valid suffixes' index
        $formattedNumber = number_format($number / 1000 ** $index, $precision);
        if ($abbreviated) {
            $unit = $suffixes[$index];
        } else {
            $unit = $suffixes[$index] ? ' ' . $suffixes[$index] : $suffixes[$index];
        }
        return $formattedNumber . $unit;
    }
}

if (!function_exists('size_shorten')) {
    /**
     * Shorten the size and append a unit.
     *
     * Units: Bytes (B), Kilobytes (KB), Megabytes (MB), Gigabytes (GB), Terabytes (TB), Petabytes (PB)
     *
     * @param int|float $size
     * @param int $precision
     * @param bool $abbreviated
     * @return string
     */
    function size_shorten(int|float $size, int $precision = 2, bool $abbreviated = false): string
    {
        if ($abbreviated) {
            $suffixes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        } else {
            $suffixes = ['Bytes', 'Kilobytes', 'Megabytes', 'Gigabytes', 'Terabytes', 'Petabytes'];
        }
        $index = log($size) / log(1024);
        $formattedSize = round(pow(1024, $index - floor($index)), $precision);
        if ($abbreviated) {
            $unit = $suffixes[$index];
        } else {
            $unit = $suffixes[$index] ? ' ' . $suffixes[$index] : $suffixes[$index];
        }
        return $formattedSize . $unit;
    }
}

if (!function_exists('create_studio_banner_from')) {
    /**
     * Creates a banner image for a studio and returns the path.
     *
     * @param Collection $images
     * @param string $absoluteFilePath
     * @return string
     */
    function create_studio_banner_from(Collection $images, string $absoluteFilePath): string
    {
        // Create a new banner image canvas
        $bannerImageCanvas = imagecreatetruecolor(1920, 1080);
        $imageCount = $images->count();

        // Get dimensions, load images and copy to the canvas
        foreach ($images as $key => $image) {
            // Get dimensions of the image
            list(${'width_' . $key}, ${'height_' . $key}) = getimagesize($image);

            // Load the image
            ${'image_' . $key} = imagecreatefromwebp($image);

            if ($imageCount == 10) {
                // Copy the image to the banner canvas
                if ($key >= 5) { // Copy image to bottom row when reaching 5th image
                    imagecopyresized($bannerImageCanvas, ${'image_' . $key}, ($key - 5) * 384, 540, 0, 0, 384, 540, ${'width_' . $key}, ${'height_' . $key});
                } else { // Copy image to top row
                    imagecopyresized($bannerImageCanvas, ${'image_' . $key}, $key * 384, 0, 0, 0, 384, 540, ${'width_' . $key}, ${'height_' . $key});
                }
            } elseif ($imageCount == 7) {
                // Copy the image to the banner canvas
                if ($key == 0) {
                    imagecopyresized($bannerImageCanvas, ${'image_' . $key}, 0, 0, 0, 0, 770, 1080, ${'width_' . $key}, ${'height_' . $key});
                } elseif ($key >= 4) { // Copy image to bottom row
                    imagecopyresized($bannerImageCanvas, ${'image_' . $key}, 770 + (($key - 4) * 384), 540, 0, 0, 384, 540, ${'width_' . $key}, ${'height_' . $key});
                } else { // Copy image to top row
                    imagecopyresized($bannerImageCanvas, ${'image_' . $key}, 770 + (($key - 1) * 384), 0, 0, 0, 384, 540, ${'width_' . $key}, ${'height_' . $key});
                }
            } elseif ($imageCount == 4) {
                // Copy the image to the banner canvas
                if ($key <= 1) {
                    imagecopyresized($bannerImageCanvas, ${'image_' . $key}, $key * 770, 0, 0, 0, 770, 1080, ${'width_' . $key}, ${'height_' . $key});
                } elseif ($key % 2 != 0) { // Copy image to bottom row
                    imagecopyresized($bannerImageCanvas, ${'image_' . $key}, 770 * 2, 540, 0, 0, 384, 540, ${'width_' . $key}, ${'height_' . $key});
                } else { // Copy image to top row
                    imagecopyresized($bannerImageCanvas, ${'image_' . $key}, 770 * 2, 0, 0, 0, 384, 540, ${'width_' . $key}, ${'height_' . $key});
                }
            }
        }

        // Save the resulting image to disk as WebP
        imagewebp($bannerImageCanvas, $absoluteFilePath);

        // Remove images from memory
        imagedestroy($bannerImageCanvas);
        foreach ($images as $key => $image) {
            imagedestroy(${'image_' . $key});
        }

        return $absoluteFilePath;
    }
}

if (!function_exists('season_of_year')) {
    /**
     * Get season of year value.
     *
     * @param Carbon|null $date
     * @return SeasonOfYear
     */
    function season_of_year(?Carbon $date = null): SeasonOfYear
    {
        $date = $date ?? now();
        $year = $date->year;

        $winter = Carbon::createFromDate($year, 1, 1);
        $spring = Carbon::createFromDate($year, 4, 1);
        $summer = Carbon::createFromDate($year, 7, 1);
        $fall = Carbon::createFromDate($year, 10, 1);

        return match (true) {
            $date >= $spring && $date < $summer => SeasonOfYear::Spring(),
            $date >= $summer && $date < $fall => SeasonOfYear::Summer(),
            $date >= $fall && $date > $winter => SeasonOfYear::Fall(),
            default => SeasonOfYear::Winter(),
        };
    }
}

if (! function_exists('yesterday')) {
    /**
     * Create a new Carbon instance for yesterday's time.
     *
     * @param DateTimeZone|string|null  $tz
     * @return \Illuminate\Support\Carbon
     */
    function yesterday(DateTimeZone|string|null $tz = null): \Illuminate\Support\Carbon
    {
        return Date::yesterday($tz);
    }
}
