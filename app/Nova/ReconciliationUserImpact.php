<?php

namespace App\Nova;

use App\Models\ReconciliationUserImpact as ReconciliationUserImpactModel;
use App\Nova\Actions\ApplyReconciliationImpact;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Stringable;

class ReconciliationUserImpact extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<ReconciliationUserImpactModel>
     */
    public static string $model = ReconciliationUserImpactModel::class;

    /**
     * The underlying model resource instance.
     *
     * @var ReconciliationUserImpactModel|null
     */
    public $resource;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'user_id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'user_id',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Store Reconciliation';

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

            BelongsTo::make('Run', 'reconciliationRun', ReconciliationRun::class)
                ->sortable(),

            BelongsTo::make('User', 'user', User::class)
                ->searchable()
                ->sortable(),

            Number::make('Missing', 'missing_transactions')->sortable(),

            Badge::make('Entitlement delta', function () {
                $before = $this->resource->before_pro || $this->resource->before_plus;
                $after = $this->resource->after_pro || $this->resource->after_plus;

                if ($before === $after && $this->resource->before_plus === $this->resource->after_plus) {
                    return 'no_change';
                }

                if (!$before && $after) {
                    return 'gain';
                }

                if ($before && !$after) {
                    return 'loss';
                }

                return 'tier_change';
            })->map([
                'no_change' => 'info',
                'gain' => 'success',
                'loss' => 'danger',
                'tier_change' => 'warning',
            ])->sortable(false),

            Heading::make('Before (persisted state)')->onlyOnDetail(),

            Boolean::make('Live is_pro flag', 'before_is_pro_flag'),
            Boolean::make('Live is_subscribed flag', 'before_is_subscribed_flag'),
            Boolean::make('Before pro', 'before_pro'),
            Boolean::make('Before plus', 'before_plus'),

            Heading::make('After (simulated post-ingest)')->onlyOnDetail(),

            Boolean::make('After pro', 'after_pro'),
            Boolean::make('After plus', 'after_plus'),

            Code::make('Before entitlements', 'before_entitlements')
                ->json()
                ->onlyOnDetail(),

            Code::make('After entitlements', 'after_entitlements')
                ->json()
                ->onlyOnDetail(),

            Text::make('Error')
                ->nullable()
                ->hideFromIndex(),

            Heading::make('Apply outcome')->onlyOnDetail(),

            DateTime::make('Applied at', 'applied_at')->sortable(),
            Number::make('Applied count', 'applied_count')->hideFromIndex(),
            Boolean::make('Applied pro', 'applied_pro'),
            Boolean::make('Applied plus', 'applied_plus'),
            Text::make('Apply error', 'applied_error')->hideFromIndex(),
        ];
    }

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): Stringable|string
    {
        return __('User Impacts');
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'reconciliation-user-impacts';
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
        return [
            (new ApplyReconciliationImpact)
                ->confirmText("Re-fetches each selected user's Apple transaction history and passes the signed JWSes through POST /v1/store/verify. Writes are additive (users.is_pro is sticky).")
                ->confirmButtonText('Apply')
                ->cancelButtonText('Cancel'),
        ];
    }
}
