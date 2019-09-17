<?php

namespace App;

use Illuminate\Support\Facades\View;

/**
 * @property mixed id
 * @property mixed name
 * @property mixed background_color
 * @property mixed text_color
 * @property mixed tint_color
 * @property mixed bar_tint_color
 * @property mixed bar_title_text_color
 * @property mixed statusbar_style
 */
class AppTheme extends KModel
{
    // Table name
    const TABLE_NAME = 'app_themes';
    protected $table = self::TABLE_NAME;

    /**
     * Generates the plist string for the theme
     *
     * @return string
     */
    function pList() {
        $view = View::make('plist.ios-theme', [
            'theme'       => $this
        ]);

        return $view->render();
    }
}
