<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class ForumReply extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\ForumReply::class;

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
    public static $group = 'Forum';

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

            BelongsTo::make('User')
                ->rules('required')
                ->searchable()
                ->sortable(),

            BelongsTo::make('Forum Thread', 'forum_thread')
                ->rules('required')
                ->searchable()
                ->sortable(),

            Text::make('Replied from IP address', 'ip_address')
                ->rules('required', 'max:255')
                ->help('The IP address that the reply was posted from.'),

            Textarea::make('Reply Content', 'content')
                ->rules('required')
                ->hideFromIndex(),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->name . 'Forum reply ID: ' . $this->id;
    }

    /**
     * Returns the user-friendly display name of the resource.
     *
     * @return string
     */
    public static function label(): string
    {
        return 'Forum Replies';
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
            <path fill="var(--sidebar-icon)" d="M92.3512039,72.485681 C97.1249369,67.7813841 99.9891767,61.8793143 99.9891767,55.4391145 C99.9891767,41.5518912 86.7095194,30.077573 69.4025674,28.0292075 C63.9344733,18.0304068 51.0887918,11 36.1079497,11 C16.1624252,11 0.00116915048,23.4290648 0.00116915048,38.7744465 C0.00116915048,45.1972873 2.86540895,51.0993572 7.63914195,55.8210131 C4.9832105,61.150235 1.1642241,65.281684 1.09478798,65.3511201 C0.00116915048,66.514175 -0.311293373,68.2153599 0.330990703,69.6908773 C0.95591575,71.1663948 2.41407419,72.1211414 4.01110487,72.1211414 C13.2981854,72.1211414 20.797286,68.6146175 25.7446093,65.3858381 C27.34164,65.7503777 28.9907477,66.0281222 30.6745735,66.2364306 C36.1253087,76.2005133 48.9189131,83.213561 63.8823962,83.213561 C67.4930742,83.213561 70.9648801,82.7969443 74.2630956,82.033147 C79.2104189,85.2445674 86.6921604,88.7684503 95.9966,88.7684503 C97.5936307,88.7684503 99.0344301,87.8137037 99.6767142,86.3381862 C100.301639,84.8626688 100.006536,83.1614839 98.9129169,81.998429 C98.8434808,81.9463519 95.0071353,77.814903 92.3512039,72.485681 L92.3512039,72.485681 Z M24.1649376,56.4632972 L21.1965437,58.3901494 C18.7489206,59.9698211 16.2492204,61.2196711 13.7148021,62.1049816 C14.1834959,61.2891073 14.6521897,60.4211558 15.1035245,59.5358453 L17.794174,54.1371873 L13.4891348,49.8842252 C11.1456658,47.5581153 8.33350311,43.7564879 8.33350311,38.7744465 C8.33350311,28.2375159 21.0576714,19.332334 36.1079497,19.332334 C51.1582279,19.332334 63.8823962,28.2375159 63.8823962,38.7744465 C63.8823962,49.3113772 51.1582279,58.2165591 36.1079497,58.2165591 C33.2437099,58.2165591 30.3794701,57.8867376 27.6020254,57.2444535 L24.1649376,56.4632972 Z M86.5012111,66.5488931 L82.2135309,70.7844962 L84.9041804,76.1831542 C85.3555152,77.0684647 85.824209,77.9364162 86.2929027,78.7522905 C83.7584845,77.86698 81.2587843,76.6171299 78.8111612,75.0374583 L75.8427672,73.1106061 L72.3883204,73.9091214 C69.6108758,74.5514055 66.746636,74.881227 63.8823962,74.881227 C54.5085205,74.881227 46.1414685,71.3920622 41.089991,66.2537896 C58.6746875,64.3790144 72.2147302,52.8179011 72.2147302,38.7744465 C72.2147302,38.1842395 72.145294,37.6113916 72.0932169,37.0385436 C83.2724317,39.5556028 91.6568427,46.8984722 91.6568427,55.4391145 C91.6568427,60.4211558 88.84468,64.2227832 86.5012111,66.5488931 Z"/>
        </svg>
    ';
}
