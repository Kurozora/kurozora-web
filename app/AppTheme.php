<?php

namespace App;

use App\Enums\iOSUIKit;
use Illuminate\Database\Eloquent\Model;

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
            'id'    => $this->id,
            'name'  => $this->name
        ];
    }

    /**
     * Generates the plist string for the theme
     *
     * @return string
     */
    function pList() {
        return '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
	<key>UIStatusBarStyle</key>
	<string>' . $this->statusbar_style . '</string>
	<key>Global</key>
	<dict>
		<key>backgroundColor</key>
		<string>' . $this->background_color . '</string>
		<key>textColor</key>
		<string>' . $this->text_color . '</string>
		<key>tintColor</key>
		<string>' . $this->tint_color . '</string>
		<key>barTintColor</key>
		<string>' . $this->bar_tint_color . '</string>
		<key>barTitleTextColor</key>
		<string>' . $this->bar_title_text_color . '</string>
	</dict>
</dict>
</plist>';
    }
}
