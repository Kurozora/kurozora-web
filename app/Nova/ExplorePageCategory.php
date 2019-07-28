<?php

namespace App\Nova;

use App\Enums\ExplorePageCategoryTypes;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use NovaConditionalFields\Condition;

class ExplorePageCategory extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\ExplorePageCategory';

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
        'id', 'title'
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

            Text::make('Title')
                ->rules('required', 'max:255')
                ->sortable()
                ->help('Please fill in a title, even if it is not displayed on the explore page.'),

            Number::make('Position/order', 'position')
                ->rules('required', 'numeric', 'min:1', 'max:100')
                ->sortable()
                ->help('This will determine the position on the explore page. Enter a number ranging from <strong>1 to 100</strong>. Lower numbers will display first.'),

            Select::make('Type')->options([
                ExplorePageCategoryTypes::Shows             => '(manual) Selected shows',
                ExplorePageCategoryTypes::MostPopularShows  => '(automatic) Most Popular shows',
                ExplorePageCategoryTypes::Genres            => '(manual) Selected genres',
            ])
                ->rules('required')
                ->sortable(),

            Select::make('Size')->options([
                'small'     => 'Small',
                'medium'    => 'Medium',
                'large'     => 'Large',
            ])
                ->rules('required')
                ->sortable(),

            BelongsToMany::make('Animes')
                ->searchable(),

            BelongsToMany::make('Genres')
                ->searchable(),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        return $this->title . ' (ID: ' . $this->id . ')';
    }

    /**
     * Returns the user-friendly display name of the resource.
     *
     * @return string
     */
    public static function label() {
        return 'Explore Page Cat.';
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [
        ];
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
}
