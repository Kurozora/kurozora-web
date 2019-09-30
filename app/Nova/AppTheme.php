<?php

namespace App\Nova;

use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Timothyasp\Color\Color;

class AppTheme extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\AppTheme';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Cosmetics';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Name')->sortable()
                ->rules('required'),

            Heading::make('Global')
                ->hideFromIndex(),

            Color::make('Background color', 'global_background_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Tinted background color', 'global_tinted_background_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Bar tint color', 'global_bar_tint_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Bar title text color', 'global_bar_title_text_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Blur background color', 'global_blur_background_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Border color', 'global_border_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Text color', 'global_text_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Text field background color', 'global_text_field_background_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Text field text color', 'global_text_field_text_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Text field placeholder text color', 'global_text_field_placeholder_text_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Tint color', 'global_tint_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Tinted button text color', 'global_tinted_button_text_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Separator color', 'global_separator_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Separator color light', 'global_separator_color_light')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Sub text color light', 'global_sub_text_color')
                ->hideFromIndex()
                ->rules('required'),

            Heading::make('Table View')
                ->hideFromIndex(),

            Color::make('Background color', 'table_view_cell_background_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Title text color', 'table_view_cell_title_text_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Sub text color', 'table_view_cell_sub_text_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Chevron color', 'table_view_cell_chevron_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Selected background color', 'table_view_cell_selected_background_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Selected title text color', 'table_view_cell_selected_title_text_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Selected sub text color', 'table_view_cell_selected_sub_text_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Selected chevron color', 'table_view_cell_selected_chevron_color')
                ->hideFromIndex()
                ->rules('required'),

            Color::make('Action default color', 'table_view_cell_action_default_color')
                ->hideFromIndex()
                ->rules('required'),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        return 'Name: "' . $this->name . '" (ID: ' . $this->id . ')';
    }

    /**
     * Returns the user-friendly display name of the resource.
     *
     * @return string
     */
    public static function label() {
        return 'Themes';
    }

    /**
     * The icon of the resource.
     *
     * @var string
     */
    public static $icon = '
        <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M7.27659574,0 C8.61618003,0 9.70212766,1.0745166 9.70212766,2.4 L9.70212766,21.6 C9.70212766,22.9254834 8.61618003,24 7.27659574,24 L2.42553191,24 C1.08594763,24 0,22.9254834 0,21.6 L0,2.388 C0,1.08 1.0793617,0 2.42553191,0 L7.27659574,0 Z M21.5981431,14.2978723 C22.9258027,14.2978723 24,15.377234 24,16.7234043 L24,21.5744681 C24,22.9140524 22.9192483,24 21.5860735,24 L10.7234043,24 L10.7234043,21.9382979 L10.7354739,21.647234 L18.0496712,14.2978723 L21.5981431,14.2978723 Z M4.72340426,18.1276596 C4.08886433,18.1276596 3.57446809,18.6420558 3.57446809,19.2765957 C3.57446809,19.9111357 4.08886433,20.4255319 4.72340426,20.4255319 C5.35794418,20.4255319 5.87234043,19.9111357 5.87234043,19.2765957 C5.87234043,18.6420558 5.35794418,18.1276596 4.72340426,18.1276596 Z M14.9044946,3.06382979 C15.5384662,3.06382979 16.1463296,3.31411546 16.593703,3.75935551 L19.9841,7.11989469 C20.9131014,8.04620999 20.9131014,9.54224388 19.9841,10.4685592 L10.7234043,19.6595745 L10.7234043,6.21741774 L13.2152862,3.75935551 C13.6626596,3.31411546 14.270523,3.06382979 14.9044946,3.06382979 Z" id="Shape" fill="var(--sidebar-icon)"></path>
        </svg>
    ';
}
