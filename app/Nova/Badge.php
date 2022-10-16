<?php

namespace App\Nova;

use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Outl1ne\NovaColorField\Color;
use Ramsey\Uuid\Uuid;

class Badge extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Badge::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Badge|null
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
        return $request->user()?->can('viewBadge') ?? false;
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

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
     */
    public function fields(Request $request): array
    {
        return [
            Heading::make('Identification')
                ->onlyOnDetail(),

            ID::make()->sortable(),

            Heading::make('Media'),

            Images::make('Symbol')
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
                        ->slider()
                        ->help('The average background color of the image.'),

                    Color::make('Text Color 1')
                        ->slider()
                        ->help('The primary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 2')
                        ->slider()
                        ->help('The secondary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 3')
                        ->slider()
                        ->help('The tertiary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 4')
                        ->slider()
                        ->help('The final post-tertiary text color that may be used if the background color is displayed.'),

                    Heading::make('Dimensions (automatically generated if empty)'),

                    Number::make('Width')
                        ->help('The maximum width available for the image.'),

                    Number::make('Height')
                        ->help('The maximum height available for the image.'),
                ]),

            Heading::make('Meta Information'),

            Text::make('Name'),

            Textarea::make('Description'),

            Color::make('Text Color')
                ->slider()
                ->rules('required'),

            Color::make('Background Color')
                ->slider()
                ->rules('required'),

            BelongsToMany::make('Users')
                ->searchable(),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $badge = $this->resource;

        return $badge->name . ' (ID: ' . $badge->id . ')';
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
     * The icon of the resource.
     *
     * @var string
     */
    public static string $icon = '
        <svg class="sidebar-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M50,100 C42.9296875,100 36.6796875,96.3671875 33.0664062,90.8789062 C26.6015625,92.2070312 19.6484375,90.3710938 14.6484375,85.3515625 C9.6484375,80.3515625 7.79296875,73.359375 9.12109375,66.9335938 C3.65234375,63.3398438 0,57.0898438 0,50 C0,42.9296875 3.6328125,36.6796875 9.12109375,33.0664062 C7.79296875,26.6601562 9.62890625,19.6484375 14.6484375,14.6484375 C19.6484375,9.6484375 26.640625,7.79296875 33.0664062,9.12109375 C36.6601562,3.65234375 42.9101562,0 50,0 C57.0898438,0 63.3203125,3.65234375 66.9335938,9.12109375 C73.3398438,7.79296875 80.3515625,9.62890625 85.3515625,14.6484375 C90.3515625,19.6484375 92.2070312,26.640625 90.8789062,33.0664062 C96.328125,36.640625 100,42.8710938 100,50 C100,57.0703125 96.3671875,63.3203125 90.8789062,66.9335938 C92.2070312,73.3398438 90.3710938,80.3515625 85.3515625,85.3515625 C80.3515625,90.3515625 73.3984375,92.2070312 66.9335938,90.8789062 C63.3398438,96.328125 57.109375,100 50,100 Z M38.046875,78.8671875 C39.3164062,82.265625 41.1523438,90.625 50,90.625 C58.5742188,90.625 60.4492188,82.890625 61.953125,78.8671875 C67.9101562,81.5625 73.2421875,84.1992188 78.7109375,78.7304688 C84.765625,72.6757812 80.625,65.859375 78.8476562,61.9726562 C82.2460938,60.703125 90.6054688,58.8671875 90.6054688,50.0195313 C90.6054688,41.4453125 82.8710938,39.5703125 78.8476562,38.0664063 C80.3515625,34.765625 84.9609375,27.5585938 78.7109375,21.3085938 C72.65625,15.2539063 65.8398438,19.3945313 61.953125,21.171875 C60.6835938,17.734375 58.8476562,9.375 50,9.375 C41.4257812,9.375 39.5507812,17.109375 38.046875,21.1328125 C34.7460938,19.6289062 27.5390625,15.0195313 21.2890625,21.2695312 C15.234375,27.3242188 19.375,34.140625 21.1523438,38.0273438 C17.734375,39.3164062 9.375,41.1523438 9.375,50 C9.375,58.5742187 17.109375,60.4492188 21.1328125,61.953125 C19.6289062,65.2539062 15.0195312,72.4609375 21.2695312,78.7109375 C27.3242188,84.765625 33.9257812,80.7421875 38.046875,78.8671875 Z"/>
        </svg>
    ';
}
