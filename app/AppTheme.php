<?php

namespace App;

use Illuminate\Support\Facades\View;

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
