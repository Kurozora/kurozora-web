<?php echo '<?xml version="1.0" encoding="UTF-8"?>' ?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
    <dict>
        <key>Version</key>
        <string>{{ $theme->version }}</string>
        <key>UIStatusBarStyle</key>
        <string>{{ $theme->statusBarStyle->stringValue() }}</string>
        <key>UIVisualEffectView</key>
        <string>{{ $theme->visualEffectViewStyle->stringValue() }}</string>
        <key>Global</key>
        <dict>
            <key>backgroundColor</key>
            <string>{{ $theme->global_background_color }}</string>
            <key>tintedBackgroundColor</key>
            <string>{{ $theme->global_tinted_background_color }}</string>
            <key>barTintColor</key>
            <string>{{ $theme->global_bar_tint_color }}</string>
            <key>barTitleTextColor</key>
            <string>{{ $theme->global_bar_title_text_color }}</string>
            <key>blurBackgroundColor</key>
            <string>{{ $theme->global_blur_background_color }}</string>
            <key>borderColor</key>
            <string>{{ $theme->global_border_color }}</string>
            <key>textColor</key>
            <string>{{ $theme->global_text_color }}</string>
            <key>textFieldBackgroundColor</key>
            <string>{{ $theme->global_text_field_background_color }}</string>
            <key>textFieldTextColor</key>
            <string>{{ $theme->global_text_field_text_color }}</string>
            <key>textFieldPlaceholderTextColor</key>
            <string>{{ $theme->global_text_field_placeholder_text_color }}</string>
            <key>tintColor</key>
            <string>{{ $theme->global_tint_color }}</string>
            <key>tintedButtonTextColor</key>
            <string>{{ $theme->global_tinted_button_text_color }}</string>
            <key>separatorColor</key>
            <string>{{ $theme->global_separator_color }}</string>
            <key>separatorColorLight</key>
            <string>{{ $theme->global_separator_color_light }}</string>
            <key>subTextColor</key>
            <string>{{ $theme->global_sub_text_color }}</string>
        </dict>
        <key>TableViewCell</key>
        <dict>
            <key>backgroundColor</key>
            <string>{{ $theme->table_view_cell_background_color }}</string>
            <key>titleTextColor</key>
            <string>{{ $theme->table_view_cell_title_text_color }}</string>
            <key>subTextColor</key>
            <string>{{ $theme->table_view_cell_sub_text_color }}</string>
            <key>chevronColor</key>
            <string>{{ $theme->table_view_cell_chevron_color }}</string>
            <key>selectedBackgroundColor</key>
            <string>{{ $theme->table_view_cell_selected_background_color }}</string>
            <key>selectedTitleTextColor</key>
            <string>{{ $theme->table_view_cell_selected_title_text_color }}</string>
            <key>selectedSubTextColor</key>
            <string>{{ $theme->table_view_cell_selected_sub_text_color }}</string>
            <key>selectedChevronColor</key>
            <string>{{ $theme->table_view_cell_selected_chevron_color }}</string>
            <key>actionDefaultColor</key>
            <string>{{ $theme->table_view_cell_action_default_color }}</string>
        </dict>
    </dict>
</plist>
