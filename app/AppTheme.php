<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
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
class AppTheme extends Model
{
    // Table name
    const TABLE_NAME = 'theme';
    protected $table = self::TABLE_NAME;

    /**
     * Formats the theme for the overview
     *
     * @return array
     */
    function formatForOverview() {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'download_link' => route('themes.download', ['theme' => $this->id])
        ];
    }

    /**
     * Generates the plist string for the theme
     *
     * @return string
     */
    function pList() {
        $view = View::make('plist.ios-theme', [
            'statusbar_style'       => $this->statusbar_style,
            'background_color'      => $this->background_color,
            'text_color'            => $this->text_color,
            'tint_color'            => $this->tint_color,
            'bar_tint_color'        => $this->bar_tint_color,
            'bar_title_text_color'  => $this->bar_title_text_color,
        ]);

        return $view->render();
    }
}
