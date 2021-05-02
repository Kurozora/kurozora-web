<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;

class AnimeStudio extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = 'App\Models\AnimeStudio';

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
        'id',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Anime Pivot';

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Anime')
                ->sortable()
                ->searchable(),

            BelongsTo::make('Studio')
                ->sortable()
                ->searchable(),
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
        <svg class="sidebar-icon" width="608px" height="524px" viewBox="0 0 608 524" xmlns="http://www.w3.org/2000/svg">
            <g fill="var(--sidebar-icon)" fill-rule="nonzero">
                <path d="M148,192 L108,192 C101.372583,192 96,197.372583 96,204 L96,244 C96,250.627417 101.372583,256 108,256 L148,256 C154.627417,256 160,250.627417 160,244 L160,204 C160,197.372583 154.627417,192 148,192 Z M148,288 L108,288 C101.372583,288 96,293.372583 96,300 L96,340 C96,346.627417 101.372583,352 108,352 L148,352 C154.627417,352 160,346.627417 160,340 L160,300 C160,293.372583 154.627417,288 148,288 Z M148,96 L108,96 C101.372583,96 96,101.372583 96,108 L96,148 C96,154.627417 101.372583,160 108,160 L148,160 C154.627417,160 160,154.627417 160,148 L160,108 C160,101.372583 154.627417,96 148,96 Z M304,160.122601 L352,160.122601 L352,32 C352,14.326888 337.673112,0 320,0 L32,0 C14.326888,0 0,14.326888 0,32 L0,432 C0,440.836556 7.163444,448 16,448 L32,448 C40.836556,448 48,440.836556 48,432 L48,48 L304,48 L304,160.122601 Z M537.203166,492.74934 C542.962083,492.74934 547.630607,497.335961 547.630607,502.993843 L547.630607,502.993843 L547.630607,513.238347 C547.630607,518.896229 542.962083,523.48285 537.203166,523.48285 L537.203166,523.48285 L266.08971,523.48285 C260.330793,523.48285 255.662269,518.896229 255.662269,513.238347 L255.662269,513.238347 L255.662269,502.993843 C255.662269,497.335961 260.330793,492.74934 266.08971,492.74934 L266.08971,492.74934 Z M576.8,192 C594.031284,192 608,205.94887 608,223.155673 L608,223.155673 L608,430.860158 C608,448.066961 594.031284,462.015831 576.8,462.015831 L576.8,462.015831 L223.2,462.015831 C205.968716,462.015831 192,448.066961 192,430.860158 L192,430.860158 L192,223.155673 C192,205.94887 205.968716,192 223.2,192 L223.2,192 Z M566.097625,228.91029 L236.097625,228.91029 L236.097625,422.91029 L566.097625,422.91029 L566.097625,228.91029 Z M244,96 C250.627417,96 256,101.372583 256,108 L256,108 L256,148 C256,154.627417 250.627417,160 244,160 L244,160 L204,160 C197.372583,160 192,154.627417 192,148 L192,148 L192,108 C192,101.372583 197.372583,96 204,96 L204,96 Z"></path>
            </g>
        </svg>
    ';
}
