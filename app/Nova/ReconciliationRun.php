<?php

namespace App\Nova;

use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Stringable;

class ReconciliationRun extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\ReconciliationRun>
     */
    public static string $model = \App\Models\ReconciliationRun::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\ReconciliationRun|null
     */
    public $resource;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'run_uuid';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'run_uuid', 'user_id_filter',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Store Reconciliation';

    /**
     * Default ordering by most-recent run.
     *
     * @var array<string, string>
     */
    public static $orderBy = [
        'started_at' => 'desc',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, mixed>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Heading::make('Identification')->onlyOnDetail(),

            ID::make()->sortable(),

            Text::make('Run UUID', 'run_uuid')->copyable()->sortable(),

            Badge::make('Source')->map([
                'history' => 'info',
                'notifications' => 'warning',
                'both' => 'success',
            ])->sortable(),

            Text::make('Environment')
                ->displayUsing(fn ($value) => $value ?: 'auto')
                ->sortable(),

            Text::make('User filter', 'user_id_filter')->hideFromIndex(),

            Heading::make('History diff')->onlyOnDetail(),

            Number::make('Users', 'users_total')->sortable(),
            Number::make('With anchors', 'users_with_anchors')->hideFromIndex(),
            Number::make('Skipped', 'users_skipped')->hideFromIndex(),
            Number::make('Errored', 'users_errored')->hideFromIndex(),
            Number::make('Apple tx', 'apple_transactions')->sortable(),
            Number::make('Present', 'local_present')->hideFromIndex(),
            Number::make('Missing', 'local_missing')->sortable(),
            Number::make('Orphan', 'local_orphan')->hideFromIndex(),

            Heading::make('Notifications diff')->onlyOnDetail(),

            DateTime::make('Since')->hideFromIndex(),
            DateTime::make('Until')->hideFromIndex(),
            Number::make('Apple notifications', 'notifications_total')->hideFromIndex(),
            Number::make('Notif. present', 'notifications_present')->hideFromIndex(),
            Number::make('Notif. missing', 'notifications_missing')->sortable(),

            Heading::make('Apple API usage')->onlyOnDetail(),

            Number::make('API calls', 'api_calls')->hideFromIndex(),
            Number::make('Retries', 'api_retries')->hideFromIndex(),
            Number::make('Rate-limit hits', 'rate_limit_hits')->hideFromIndex(),

            Heading::make('Timing')->onlyOnDetail(),

            DateTime::make('Started', 'started_at')->sortable(),
            DateTime::make('Completed', 'completed_at')->sortable(),

            HasMany::make('Rows', 'rows', ReconciliationRow::class),
            HasMany::make('User impacts', 'userImpacts', ReconciliationUserImpact::class),
        ];
    }

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): Stringable|string
    {
        return __('Reconciliation Runs');
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'reconciliation-runs';
    }

    /**
     * Get the cards available for the resource.
     *
     * @return array<int, mixed>
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<int, mixed>
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<int, mixed>
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<int, mixed>
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }
}
