<?php

// Create a deeplinked iOS URL
if (!function_exists('ios_app_url')) {
    function ios_app_url($path) {
        return config('app.ios_app_protocol') . $path;
    }
}
