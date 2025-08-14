<?php

namespace App\Nova;

use App\Enums\ParentalGuideCategory;
use App\Enums\ParentalGuideDepiction;
use App\Enums\ParentalGuideFrequency;
use App\Enums\ParentalGuideRating;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Card;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Lenses\Lens;

class ParentalGuideEntry extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\ParentalGuideEntry>
     */
    public static $model = \App\Models\ParentalGuideEntry::class;

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
        'id', 'reason'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Heading::make('Identification')
                ->onlyOnDetail(),

            ID::make()->sortable(),

            BelongsTo::make('User')
                ->searchable()
                ->sortable()
                ->rules('required')
                ->help('The user who created this parental guide entry.'),

            MorphTo::make('Model')
                ->types([
                    Anime::class,
                    Manga::class,
                    Game::class,
                ])
                ->searchable()
                ->sortable(),

            Heading::make('Meta information'),

            Select::make('Category')
                ->options(ParentalGuideCategory::asSelectArray())
                ->displayUsingLabels()
                ->sortable()
                ->rules('required')
                ->help('Indicates the type of content that is being described in this parental guide entry.'),

            Select::make('Rating')
                ->options(ParentalGuideRating::asSelectArray())
                ->displayUsingLabels()
                ->sortable()
                ->rules('required')
                ->help('Indicates the severity of the content in the media.'),

            Select::make('Frequency')
                ->options(ParentalGuideFrequency::asSelectArray())
                ->displayUsingLabels()
                ->sortable()
                ->help('Indicates how often the content appears in the media.'),

            Select::make('Depiction')
                ->options(ParentalGuideDepiction::asSelectArray())
                ->displayUsingLabels()
                ->sortable()
                ->help('Indicates how the content is depicted in the media.'),

            Textarea::make('Reason')
                ->sortable()
                ->rules('required', 'max:500')
                ->help('Provide a reason for this parental guide entry.'),

            Boolean::make('Spoiler')
                ->rules('boolean')
                ->help('Indicates whether this entry contains spoilers.'),

            Boolean::make('Hidden')
                ->rules('boolean')
                ->help('Indicates whether this entry is hidden from public view.'),
        ];
    }

    /**
     * Get the cards available for the resource.
     *
     * @return array<int, Card>
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<int, Filter>
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<int, Lens>
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<int, Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }
}
