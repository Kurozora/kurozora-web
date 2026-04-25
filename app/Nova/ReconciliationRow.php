<?php

namespace App\Nova;

use App\Models\ReconciliationRow as ReconciliationRowModel;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Stringable;

class ReconciliationRow extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<ReconciliationRowModel>
     */
    public static string $model = ReconciliationRowModel::class;

    /**
     * The underlying model resource instance.
     *
     * @var ReconciliationRowModel|null
     */
    public $resource;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'transaction_id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'user_id', 'transaction_id', 'original_transaction_id', 'notification_uuid', 'product_id',
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

            Badge::make('Source')->map([
                ReconciliationRowModel::SOURCE_HISTORY => 'info',
                ReconciliationRowModel::SOURCE_NOTIFICATIONS => 'warning',
            ])->sortable(),

            Badge::make('Status')->map([
                ReconciliationRowModel::STATUS_PRESENT => 'success',
                ReconciliationRowModel::STATUS_MISSING => 'danger',
                ReconciliationRowModel::STATUS_ORPHAN => 'warning',
            ])->sortable(),

            BelongsTo::make('User', 'user', User::class)
                ->nullable()
                ->searchable()
                ->sortable(),

            Text::make('Transaction ID', 'transaction_id')->copyable(),
            Text::make('Original TX ID', 'original_transaction_id')->hideFromIndex()->copyable(),
            Text::make('Product', 'product_id')->sortable(),
            Text::make('Notification UUID', 'notification_uuid')->hideFromIndex()->copyable(),

            Code::make('Decoded payload', 'payload')
                ->json()
                ->onlyOnDetail(),
        ];
    }

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): Stringable|string
    {
        return __('Reconciliation Rows');
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'reconciliation-rows';
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
        return [
            new Filters\ReconciliationRowStatus,
            new Filters\ReconciliationRowSource,
        ];
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
