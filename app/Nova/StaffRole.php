<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class StaffRole extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\StaffRole::class;

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
        'name',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'People';

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            Text::make('Name')
                ->sortable()
                ->help('The name of the role. For example, Color Design, Voice Actor, etc.')
                ->rules('unique:' . \App\Models\StaffRole::TABLE_NAME . ',name')
                ->required(),
        ];
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
        <svg class="sidebar-icon" viewBox="0 0 139 103" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M12.5488,81.05466 L57.3242,81.05466 L57.3242,73.1933 L12.6953,73.1933 C9.3261,73.1933 7.8613,71.8261 7.8613,68.3593 L7.8613,12.6465 C7.8613,9.2285 9.3261,7.8613 12.6953,7.8613 L109.2778,7.8613 C112.5978,7.8613 114.0628,9.2285 114.0628,12.6465 L114.0628,56.5429 L121.9238,56.5429 L121.9238,12.5488 C121.9238,4.0039 117.7738,0 109.4238,0 L12.5488,0 C4.1992,0 0,4.0039 0,12.5488 L0,68.5058 C0,77.05075 4.1992,81.05466 12.5488,81.05466 Z M74.8535,102.0996 L128.3688,102.0996 C135.2048,102.0996 138.7208,98.6328 138.7208,91.69919 L138.7208,74.16989 C138.7208,67.1875 135.2048,63.7207 128.3688,63.7207 L74.8535,63.7207 C67.8711,63.7207 64.5019,67.1875 64.5019,73.9258 L64.5019,91.69919 C64.5019,98.6328 67.8711,102.0996 74.8535,102.0996 Z M122.6558,85.44919 C119.5308,85.44919 117.0408,82.91012 117.0408,79.78512 C117.0408,76.75778 119.5308,74.21872 122.6558,74.21872 C125.7328,74.21872 128.2228,76.75778 128.2228,79.78512 C128.2228,82.91012 125.7328,85.44919 122.6558,85.44919 Z M36.4746,97.5586 L58.1054,97.5586 C57.5195,95.3613 57.373,93.74997 57.373,89.64841 L36.4746,89.64841 C34.3261,89.64841 32.5195,91.45505 32.5195,93.65231 C32.5195,95.8008 34.3261,97.5586 36.4746,97.5586 Z" />
        </svg>
    ';
}
