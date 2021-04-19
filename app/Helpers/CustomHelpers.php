<?php

// Create a deeplinked iOS URL
use App\Helpers\Settings;

if (!function_exists('ios_app_url')) {
    function ios_app_url($path) {
        return config('app.ios_app_protocol') . $path;
    }
}

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
