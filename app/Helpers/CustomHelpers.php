<?php

use App\Enums\SeasonOfYear;
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

if (!function_exists('ordinal_number')) {
    function ordinal_number(int|float $number): bool|string
    {
        return (new NumberFormatter(app()->getLocale(), NumberFormatter::ORDINAL))->format($number);
    }
}

if (!function_exists('round_to_nearest_quarter')) {
    /**
     * Rounds the given integer to the nearest quarter.
     *
     * @param int|float $number
     *
     * @return int
     */
    function round_to_nearest_quarter(int|float $number): int
    {
        if ($number == 0) {
            return 0; // Special case: 0 is already a multiple of any power of 10
        }

        $magnitude = pow(10, floor(log10(abs($number)))); // Calculate the magnitude of the number

        if ($number > 100) {
            $quarter = $magnitude / 4; // Determine the quarter for the current magnitude
        } else {
            $quarter = $magnitude;
        }

        $roundedValue = floor($number / $quarter) * $quarter;

        return (int) $roundedValue;
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
            [${'width_' . $key}, ${'height_' . $key}] = getimagesize($image);

            // Load the image
            ${'image_' . $key} = imagecreatefromwebp($image);

            if ($imageCount == 10) {
                // Copy the image to the banner canvas
                if ($key >= 5) { // Copy image to bottom row when reaching 5th image
                    imagecopyresized($bannerImageCanvas, ${'image_' . $key}, ($key - 5) * 384, 540, 0, 0, 384, 540, ${'width_' . $key}, ${'height_' . $key});
                } else { // Copy image to top row
                    imagecopyresized($bannerImageCanvas, ${'image_' . $key}, $key * 384, 0, 0, 0, 384, 540, ${'width_' . $key}, ${'height_' . $key});
                }
            } else if ($imageCount == 7) {
                // Copy the image to the banner canvas
                if ($key == 0) {
                    imagecopyresized($bannerImageCanvas, ${'image_' . $key}, 0, 0, 0, 0, 770, 1080, ${'width_' . $key}, ${'height_' . $key});
                } else if ($key >= 4) { // Copy image to bottom row
                    imagecopyresized($bannerImageCanvas, ${'image_' . $key}, 770 + (($key - 4) * 384), 540, 0, 0, 384, 540, ${'width_' . $key}, ${'height_' . $key});
                } else { // Copy image to top row
                    imagecopyresized($bannerImageCanvas, ${'image_' . $key}, 770 + (($key - 1) * 384), 0, 0, 0, 384, 540, ${'width_' . $key}, ${'height_' . $key});
                }
            } else if ($imageCount == 4) {
                // Copy the image to the banner canvas
                if ($key <= 1) {
                    imagecopyresized($bannerImageCanvas, ${'image_' . $key}, $key * 770, 0, 0, 0, 770, 1080, ${'width_' . $key}, ${'height_' . $key});
                } else if ($key % 2 != 0) { // Copy image to bottom row
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
     * Seasons aren’t year specific, but month specific.
     * As such, any date passed will have its year set
     * to `0`.
     *
     * @param Carbon|null $date
     * @return SeasonOfYear
     */
    function season_of_year(?Carbon $date = null): SeasonOfYear
    {
        $date = ($date ?? now())
            ->copy()
            ->setYear(0);

        $winter = SeasonOfYear::Winter()->startDate();
        $spring = SeasonOfYear::Spring()->startDate();
        $summer = SeasonOfYear::Summer()->startDate();
        $fall = SeasonOfYear::Fall()->startDate();

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

if (! function_exists('generate_random_color')) {
    /**
     * Generate a random color based on a seed.
     *
     * @param $number
     *
     * @return string
     */
    function generate_random_color($number): string
    {
        // Ensure the number is positive
        $number = abs($number);

        // Use the number to seed the random color generation
        srand($number);

        // Generate random RGB values
        $red = rand(0, 175);
        $green = rand(0, 175);
        $blue = rand(0, 175);

        // Format the RGB values into a hexadecimal color code
        return sprintf('#%02x%02x%02x', $red, $green, $blue);
    }
}

if (! function_exists('strip_html')) {
    /**
     * Strips the given string from any HTML tags,
     * and convers breaks to new line among other stuff.
     *
     * @param string $string
     *
     * @return string
     */
    function strip_html(string $string): string
    {
        // Convert breaks to new line
        $string = str_replace(
            ['<br>', '<br />', '<br/>', '<br >'],
            "\\n",
            $string
        );

        // Convert nbsp to space
        $string = str_replace("\xc2\xa0", ' ', $string);

        // Remove control characters
        $string = preg_replace('~[[:cntrl:]]~', '', $string);

        // Strip any leftover tags
        $string = strip_tags($string);

        // Remove any newlines at the end
        $string = str_replace('\\n', "\n", $string);

        // Trim and return
        return trim($string);
    }
}

if (! function_exists('str_index')) {
    /**
     * Get the index of the string based on the first character.
     * If the character is an alphabet, then the index is equivalent
     * to the alphabet letter. Otherwise, the index is equivalent to `.`.
     *
     * `.` represents all non-alphabet characters.
     *
     * @param string $string
     *
     * @return string
     */
    function str_index(string $string): string
    {
        // Trim and get the first character
        $character = str($string)
            ->trim()
            ->charAt(0);

        // Index under '.' by default.
        $index = '.';

        if ($character) {
            $index = match(ctype_alpha($character)) {
                true => $character,
                false => '.'
            };
        }

        // Trim and return
        return $index;
    }
}

if (! function_exists('parse_user_agent')) {
    /**
     * Parse the given user agent and return the components as array.
     *
     * @param null|string $userAgent
     *
     * @return array
     */
    function parse_user_agent(?string $userAgent): array
    {
        if (!$userAgent) {
            return [];
        }

        // Example input:
        // "Example App/1.0.0 (com.example.app; build:9999; iOS 18.5.0) Client/1.0.0"
        $matches = [];
        preg_match('/^(?<appName>[^\/]+)\/(?<version>\S+) \((?<meta>[^)]+)\) (?<clientInfo>.+)$/', $userAgent, $matches);

        // Split the meta info: "com.example.app; build:9999; iOS 18.5.0"
        $metaParts = array_map('trim', explode(';', $matches['meta'] ?? ''));
        $bundle = $build = $os = null;

        foreach ($metaParts as $part) {
            if (str_starts_with($part, 'build:')) {
                $build = substr($part, 6);
            } else if (str_starts_with($part, 'iOS') || str_starts_with($part, 'Android') || str_starts_with($part, 'macOS') || str_starts_with($part, 'Linux') || str_starts_with($part, 'Windows')) {
                $os = $part;
            } else {
                $bundle = $part;
            }
        }

        return [
            'app_name' => $matches['appName'] ?? null,
            'app_version' => $matches['version'] ?? null,
            'bundle' => $bundle,
            'build' => $build,
            'os' => $os,
            'client_info' => $matches['clientInfo'] ?? null,
        ];
    }
}
