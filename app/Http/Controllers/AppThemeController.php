<?php

namespace App\Http\Controllers;

use App\AppTheme;
use App\Helpers\JSONResult;

class AppThemeController extends Controller
{
    /**
     * Return an overview of themes
     */
    function overview() {
        $themes = AppTheme::all();

        $formattedThemes = $themes->map(function(AppTheme $theme) {
            return $theme->formatForOverview();
        });

        (new JSONResult())->setData([
            'themes' => $formattedThemes
        ])->show();
    }
}
