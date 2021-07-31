<?php

use App\Helpers\Settings;

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

        return $settings->settings($key, $value, $setEmptyValue);
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
        $index = (int)log(abs($number), 1000);
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
