<?php

namespace App\Nova;

use App\Enums\TimeoutReason;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Timeout extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Timeout::class;

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
        'reason_key',
        'note',
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
     * Determine if the current user can create the resource.
     *
     * @param Request $request
     *
     * @return bool
     */
    public static function authorizedToCreate(Request $request): bool
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
        return self::isModerator($request);
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
            Heading::make('Identification'),

            ID::make()->sortable(),

            BelongsTo::make('User')
                ->searchable()
                ->sortable(),

            BelongsTo::make('Issued By', 'issuedBy', User::class)
                ->nullable()
                ->searchable(),

            Heading::make('Reason'),

            Select::make('Reason', 'reason_key')
                ->options(TimeoutReason::asSelectArray())
                ->displayUsingLabels()
                ->rules('required')
                ->sortable(),

            Textarea::make('Note')
                ->rules('required')
                ->help('Internal note shown to staff and to the suspended user.'),

            Heading::make('Schedule'),

            Boolean::make('Is Permanent')
                ->sortable()
                ->help('Permanent suspensions never auto-expire.'),

            DateTime::make('Expires At')
                ->nullable()
                ->sortable()
                ->help('Leave empty when the timeout is permanent.'),

            DateTime::make('Expiry Notified At')
                ->nullable()
                ->onlyOnDetail()
                ->help('Set automatically by the expiry notification sweeper.'),

            Heading::make('Revocation'),

            DateTime::make('Revoked At')
                ->nullable()
                ->sortable(),

            BelongsTo::make('Revoked By', 'revokedBy', User::class)
                ->nullable()
                ->searchable(),

            Heading::make('Relationships'),

            HasOne::make('Appeal', 'appeal', TimeoutAppeal::class),

            Text::make('Status', function () {
                return $this->resource->isActive() ? 'Active' : 'Inactive';
            })
                ->onlyOnIndex()
                ->onlyOnDetail(),
        ];
    }

    /**
     * Indicates whether the requesting user is allowed to moderate timeouts.
     *
     * @param Request $request
     *
     * @return bool
     */
    private static function isModerator(Request $request): bool
    {
        return $request->user() !== null && $request->user()->hasAnyRole(['superAdmin', 'admin', 'mod']);
    }
}
