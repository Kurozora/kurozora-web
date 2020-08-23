<?php

namespace App\Nova;

use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
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

            Images::make('Screenshot')
                ->setFileName(function($originalFilename, $extension, $model){
                    return md5($originalFilename) . '.' . $extension;
                })
                ->setName(function($originalFilename, $model){
                    return md5($originalFilename);
                })
                ->required()
                ->singleMediaRules('dimensions:min-width=375,min-height=667,max_width=768,max-height=1024')
                ->help('Screenshot should have a minimum dimension of 375x667 and a maximum dimension of 768x1024. i.e a screenshot on iPhone 6...iPhone 11 Pro.'),

            Text::make('Name')->sortable()
                ->rules('required'),

            Text::make('Download link', function () {
                return '
                    <a href="' . route('api.themes.download', ['theme' => $this->id]) . '" target="_blank" class="btn btn-default btn-primary">Download</a>
                ';
            })
                ->asHtml()
                ->readonly(),

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
        <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
            <path fill="var(--sidebar-icon)" d="M112 424c13.25 0 24-10.75 24-24 0-13.26-10.75-24-24-24s-24 10.74-24 24c0 13.25 10.75 24 24 24zm368-136h-97.61l69.02-69.02c12.5-12.5 12.5-32.76 0-45.25L338.27 60.59c-6.25-6.25-14.44-9.37-22.63-9.37s-16.38 3.12-22.63 9.37L224 129.61V32c0-17.67-14.33-32-32-32H32C14.33 0 0 14.33 0 32v368c0 61.86 50.14 112 112 112h368c17.67 0 32-14.33 32-32V320c0-17.67-14.33-32-32-32zM176 400c0 17.88-7.41 34.03-19.27 45.65-3.65 3.57-7.7 6.53-11.99 9.05-.86.51-1.76.96-2.64 1.43-4.47 2.34-9.12 4.31-14.02 5.57-5.16 1.35-10.48 2.29-16.06 2.29H112c-35.29 0-64-28.71-64-64v-96h128V400zm0-144H48v-80h128v80zm0-128H48V48h128v80zm48 69.49l91.65-91.65 90.51 90.51L224 378.51V197.49zM464 464H206.39l128-128H464v128z"/>
        </svg>
    ';
}
