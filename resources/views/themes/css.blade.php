:root {
    --tint-color: {{ $theme->global_tint_color }};
}

.app-theme {
    --bg-primary-color: {{ $theme->global_background_color }};
    --bg-secondary-color: {{ $theme->table_view_cell_background_color }};
    --bg-tertiary-color: {{ $theme->table_view_cell_selected_background_color }};
    --border-color: {{ $theme->global_border_color }};
    --bg-blur-color: {{ $theme->global_blur_background_color }};
    --bg-tint-color: {{ $theme->global_tinted_background_color }};
    --btn-tinted-text-color: {{ $theme->global_tinted_button_text_color }};
    --primary-text-color: {{ $theme->global_text_color }};
    --secondary-text-color: {{ $theme->global_sub_text_color }};
    --primary-separator-color: {{ $theme->global_separator_color }};
    --secondary-separator-color: {{ $theme->global_separator_color_light }};
}
