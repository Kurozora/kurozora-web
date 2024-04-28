<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Validator;

class MediaType extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\MediaType::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\MediaType|null
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
    public static $group = 'Media';

    /**
     * Determine if this resource uses Laravel Scout.
     *
     * @return bool
     */
    public static function usesScout(): bool
    {
        return false;
    }

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

            Heading::make('Meta information'),

            Select::make('Type')
                ->options([
                    'anime' => 'anime',
                    'manga' => 'manga'
                ])
                ->sortable()
                ->required(),

            Text::make('Name')
                ->sortable()
                ->help('The name of the media type.')
                ->required(),

            Text::make('Description')
                ->sortable()
                ->help('An explanation of what the media type means.')
                ->required(),

            HasMany::make('Anime'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $mediaType = $this->resource;

        return $mediaType->name . ' (ID: ' . $mediaType->id . ')';
    }

    /**
     * Determine if this resource is available for navigation.
     *
     * @param Request $request
     * @return bool
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->can('viewMediaType');
    }

    /**
     * Handle any post-validation processing.
     *
     * @param NovaRequest $request
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     * @throws ValidationException
     */
    protected static function afterValidation(NovaRequest $request, $validator): void
    {
        $resourceID = $request->resourceId;
        $type = $request->post('type');
        $name = $request->post('name');

        $unique = Rule::unique(\App\Models\MediaType::TABLE_NAME)->where(function ($query) use($resourceID, $type, $name) {
            if ($resourceID) {
                $query->whereNotIn('id', [$resourceID]);
            }

            return $query->where('type', $type)->where('name', $name);
        });

        $uniqueValidator = Validator::make($request->only('name'), [
            'name' => [$unique],
        ], [
            'name' => __('validation.unique')
        ]);

        $uniqueValidator->validate();
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
}
