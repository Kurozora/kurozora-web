<?php

namespace App\Nova;

use App\Enums\MediaCollection;
use App\Enums\StatusBarStyle;
use App\Enums\VisualEffectViewStyle;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Exception;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Outl1ne\NovaColorField\Color;
use Ramsey\Uuid\Uuid;

class AppTheme extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\AppTheme::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\AppTheme|null
     */
    public $resource;

    /**
     * Determine if the resource should be available for the given request.
     *
     * @param Request $request
     * @return bool
     */
    public static function authorizedToViewAny(Request $request): bool
    {
        return $request->user()?->can('viewAppTheme') ?? false;
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Cosmetics';

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function fields(Request $request): array
    {
        return [
            Heading::make('Identification')
                ->onlyOnDetail(),

            ID::make()->sortable(),

            Heading::make('Media'),

            Images::make('Screenshot', MediaCollection::Screenshot)
                ->showStatistics()
                ->setFileName(function($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function($originalFilename, $model) {
                    return $this->resource->name;
                })
                ->customPropertiesFields([
                    Heading::make('Colors (automatically generated if empty)'),

                    Color::make('Background Color')
                        ->help('The average background color of the image.'),

                    Color::make('Text Color 1')
                        ->help('The primary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 2')
                        ->help('The secondary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 3')
                        ->help('The tertiary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 4')
                        ->help('The final post-tertiary text color that may be used if the background color is displayed.'),

                    Heading::make('Dimensions (automatically generated if empty)'),

                    Number::make('Width')
                        ->help('The maximum width available for the image.'),

                    Number::make('Height')
                        ->help('The maximum height available for the image.'),
                ])
                ->singleMediaRules('dimensions:min-width=375,min-height=667,max_width=1170,max-height=2532')
                ->help('Screenshot should have a minimum dimension of 375x667 and a maximum dimension of 768x1024. i.e a screenshot on iPhone 6...iPhone 12 Pro Max.')
                ->required(),

            Heading::make('Meta information'),

            Text::make('Name')
                ->required()
                ->sortable(),

            Text::make('Version')
                ->default('1.0')
                ->required()
                ->sortable(),

            File::make('Download link')
                ->displayUsing(function () {
                    return ' ';
                })
                ->download(function () {
                    return $this->resource->download();
                })
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->readonly(),

            Heading::make('Root'),

            Select::make('UI Status Bar Style')
                ->options(StatusBarStyle::asSelectArray())
                ->displayUsingLabels()
                ->hideFromIndex()
                ->required(),

            Select::make('UI Visual Effect View')
                ->options(VisualEffectViewStyle::asSelectArray())
                ->displayUsingLabels()
                ->hideFromIndex()
                ->required(),

            Heading::make('Global'),

            Color::make('Background color', 'global_background_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Tinted background color', 'global_tinted_background_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Bar tint color', 'global_bar_tint_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Bar title text color', 'global_bar_title_text_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Blur background color', 'global_blur_background_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Border color', 'global_border_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Text color', 'global_text_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Text field background color', 'global_text_field_background_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Text field text color', 'global_text_field_text_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Text field placeholder text color', 'global_text_field_placeholder_text_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Tint color', 'global_tint_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Tinted button text color', 'global_tinted_button_text_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Separator color', 'global_separator_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Separator color light', 'global_separator_color_light')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Sub text color light', 'global_sub_text_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Heading::make('Table View')
                ->hideFromIndex(),

            Color::make('Background color', 'table_view_cell_background_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Title text color', 'table_view_cell_title_text_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Sub text color', 'table_view_cell_sub_text_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Chevron color', 'table_view_cell_chevron_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Selected background color', 'table_view_cell_selected_background_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Selected title text color', 'table_view_cell_selected_title_text_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Selected sub text color', 'table_view_cell_selected_sub_text_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Selected chevron color', 'table_view_cell_selected_chevron_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),

            Color::make('Action default color', 'table_view_cell_action_default_color')
                ->slider()
                ->displayAs('hex8')
                ->saveAs('hex8')
                ->hideFromIndex()
                ->required(),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        return 'Name: "' . $this->resource->name . '" (ID: ' . $this->resource->id . ')';
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     * @return array
     */
    public function cards(Request $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function filters(Request $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function lenses(Request $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function actions(Request $request): array
    {
        return [];
    }
    /**
     * Returns the user-friendly display name of the resource.
     *
     * @return string
     */
    public static function label(): string
    {
        return 'Themes';
    }
}
