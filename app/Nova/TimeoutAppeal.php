<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class TimeoutAppeal extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\TimeoutAppeal::class;

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
        'message',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Users';

    /**
     * Determine if the resource should be available for the given request.
     *
     * @param Request $request
     *
     * @return bool
     */
    public static function authorizedToViewAny(Request $request): bool
    {
        return self::isModerator($request);
    }

    /**
     * Determine if the current user can view the given resource.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function authorizedToView(Request $request): bool
    {
        return self::isModerator($request);
    }

    /**
     * Determine if the current user can update the given resource.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }

    /**
     * Determine if the current user can delete the given resource.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function authorizedToDelete(Request $request): bool
    {
        return self::isModerator($request);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     *
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Timeout', 'timeout', Timeout::class)
                ->searchable(),

            Textarea::make('Message')
                ->readonly()
                ->alwaysShow(),

            DateTime::make('Created At')
                ->sortable()
                ->exceptOnForms(),
        ];
    }

    /**
     * Indicates whether the requesting user is allowed to view appeals.
     *
     * @param Request $request
     *
     * @return bool
     */
    private static function isModerator(Request $request): bool
    {
        return $request->user() !== null
            && $request->user()->hasAnyRole(['superAdmin', 'admin', 'mod']);
    }
}
