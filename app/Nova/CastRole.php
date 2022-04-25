<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

class CastRole extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\CastRole::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\CastRole|null
     */
    public $resource;

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
        'id', 'name',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Anime Cast';

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            Heading::make('Identification'),

            ID::make()->sortable(),

            Heading::make('Meta information'),

            Text::make('Name')
                ->sortable()
                ->help('The name of the role.')
                ->required(),

            Text::make('Description')
                ->sortable()
                ->help('A short description of the role.')
                ->required(),

            HasMany::make('Cast'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $castRole = $this->resource;

        return $castRole->name . ' (ID: ' . $castRole->id . ')';
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     * @return bool
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->can('viewCastRole');
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
            <path fill="var(--sidebar-icon)" d="M49.9999699,100 C77.3529105,100 100.000371,77.3039091 100.000371,50 C100.000371,22.6470594 77.3039191,0 49.9509785,0 C22.6470293,0 0,22.6470594 0,50 C0,77.3039091 22.696121,100 49.9999699,100 Z M49.9999699,91.6666499 C26.8626957,91.6666499 8.38231131,73.1372742 8.38231131,50 C8.38231131,26.8627258 26.8137043,8.33335007 49.9509785,8.33335007 C73.0882527,8.33335007 91.6176285,26.8627258 91.6669181,50 C91.7160127,73.1372742 73.1372441,91.6666499 49.9999699,91.6666499 Z M73,56.25 C69.828125,56.25 69.28125,56.1328125 67.359375,56.78125 C65.9921875,57.2421875 64.5234375,57.5 63,57.5 C61.4765625,57.5 60.0078125,57.2421875 58.640625,56.78125 C56.71875,56.1328125 56.1796875,56.25 53,56.25 C47.4765625,56.25 43,60.7265625 43,66.25 L43,67.5 C43,68.8828125 44.1171875,70 45.5,70 L80.5,70 C81.8828125,70 83,68.8828125 83,67.5 L83,66.25 C83,60.7265625 78.5234375,56.25 73,56.25 Z M50.5004883,42.5 C51.3632813,42.5 52.0625,41.8867187 52.0625,41.1298828 L52.0625,30 L48.9848633,40.7973633 C48.7382813,41.6621094 49.484375,42.5 50.5004883,42.5 Z M77.0151367,40.7973633 L73.9375,30 L73.9375,41.1298828 C73.9375,41.8867187 74.6367188,42.5 75.4995117,42.5 C76.515625,42.5 77.2617187,41.6621094 77.0151367,40.7973633 Z M64.5166359,55 L64.5166359,44.3191166 L70.077634,42.3604446 L70.077634,40.4017726 L55.922366,40.4017726 L55.922366,42.3604446 L61.4833641,44.3191166 L61.4833641,55 L52.06,50.5507541 C52.1086587,50.3781461 52.1598452,50.197581 52.2135594,50.0084468 C52.8853027,47.6464107 53.9001848,44.0773431 53.9001848,39.5950446 C53.9001848,35.7119773 59.7063725,31.7781069 63,30 C66.2936275,31.7781069 72.0998152,35.7119773 72.0998152,39.5950446 C72.0998152,44.0773431 73.1146973,47.6464107 73.7864406,50.0078347 C73.8401548,50.1963569 73.8913413,50.377534 73.94,50.5495299 L64.5166359,55 Z M28.6237958,56.9299948 C29.9395046,56.9353824 30.5737847,57.008695 31.8585937,57.4421875 C33.1574219,57.8800781 34.5527344,58.125 36,58.125 C37.4472656,58.125 38.8425781,57.8800781 40.1414062,57.4421875 C41.4262153,57.008695 42.0641707,56.9353824 43.3794712,56.9299948 L44.5755081,56.9344857 L44.5755081,56.9344857 C41.2188625,59.1368707 39,62.9547742 39,67.296073 L39,68.8405998 C39,69.251081 39.0789862,69.6426194 39.2224947,70.0006257 L19.375,70 C18.1160645,70 17.0888821,69.0252733 17.0054692,67.7878362 L17,67.625 L17,66.4375 C17,61.1902344 21.2527344,56.9375 26.5,56.9375 L28.6237958,56.9299948 Z M24.1254639,43.875 C24.9451172,43.875 25.609375,43.2923828 25.609375,42.5733887 L25.609375,32 L22.6856201,42.2574951 C22.4513672,43.0790039 23.1601562,43.875 24.1254639,43.875 Z M37.4408041,55.75 L37.4408041,45.6031608 L42.7237523,43.7424224 L42.7237523,41.881684 L29.2762477,41.881684 L29.2762477,43.7424224 L34.5591959,45.6031608 L34.5591959,55.75 L25.607,51.5232164 C25.6532258,51.3592388 25.7018529,51.187702 25.7528814,51.0080244 C26.3910375,48.7640902 27.3551756,45.3734759 27.3551756,41.1152923 C27.3551756,37.4263784 32.8710538,33.6892016 36,32 C39.1289462,33.6892016 44.6448244,37.4263784 44.6448244,41.1152923 C44.6448244,45.3734759 45.6089625,48.7640902 46.2471186,51.007443 C46.2981471,51.186539 46.3467742,51.3586573 46.393,51.5220534 L37.4408041,55.75 Z"/>
        </svg>
    ';
}
