<?php

namespace App\Nova;

use Laravel\Nova\Actions\Action;
use Laravel\Nova\Card;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Lenses\Lens;
use Stringable;

class APIClientToken extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\APIClientToken>
     */
    public static string $model = \App\Models\APIClientToken::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\APIClientToken|null
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
        'id', 'client_id', 'name', 'token'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'User';

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

            Heading::make('Meta information'),

            BelongsTo::make('User')
                ->readonly(function () {
                    return $this->resource->exists;
                })
                ->searchable(),

            Text::make('Description')
                ->rules('max:255')
                ->help('The identifier description is displayed as the app name to users when requesting access to ' . config('app.name') . '.'),

            Text::make('Identifier')
                ->readonly(function () {
                    return $this->resource->exists;
                })
                ->rules('max:255')
                ->help('A reverse-domain name style string for the identifier (i.e., ' . config('app.ios.bundle_id') . ').'),

            Password::make('Token')
                ->readonly()
                ->onlyOnDetail(),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $apiClientToken = $this->resource;

        return $apiClientToken->description . ' (ID: ' . $apiClientToken->id . ')';
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return Stringable|string
     */
    public static function label(): Stringable|string
    {
        return __('API Client Tokens');
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey(): string
    {
        return 'api-client-tokens';
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
