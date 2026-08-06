<?php

namespace App\Nova;

use App\Enums\StoreProductType;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Card;
use Laravel\Nova\Exceptions\HelperNotSupported;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Lenses\Lens;

class StoreProduct extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\StoreProduct::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\StoreProduct|null
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
        'id', 'product_id', 'name',
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Store';

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     *
     * @return array
     * @throws HelperNotSupported
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Heading::make('Identification')
                ->onlyOnDetail(),

            ID::make()->sortable(),

            Text::make('Product ID', 'product_id')
                ->copyable()
                ->sortable()
                ->creationRules('unique:store_products,product_id')
                ->updateRules('unique:store_products,product_id,{{resourceId}}')
                ->help('The App Store product identifier. For example: app.kurozora.autoRenewableSubscription.kPlus1Month')
                ->required(),

            Heading::make('Meta information'),

            Text::make('Name')
                ->sortable()
                ->help('The display name of the product. For example: Kurozora+ 1 Month')
                ->required(),

            Text::make('Platform')
                ->sortable()
                ->help('The store platform the product is sold on. For example: app-store')
                ->required(),

            Select::make('Type')
                ->options(StoreProductType::asSelectArray())
                ->displayUsing(function (?StoreProductType $storeProductType) {
                    return $storeProductType?->key;
                })
                ->sortable()
                ->help('The purchase model of the product.')
                ->required(),

            Text::make('Subscription Group', 'subscription_group')
                ->hideFromIndex()
                ->nullable()
                ->help('The App Store subscription group identifier. Only for auto-renewing subscriptions.'),

            Number::make('Price (USD milliunits)', 'price_usd_milliunits')
                ->sortable()
                ->nullable()
                ->help('The base price in thousandths of a US dollar. For example: 3990 for $3.99'),

            Number::make('Duration (days)', 'duration_days')
                ->hideFromIndex()
                ->nullable()
                ->help('The subscription length in days. For example: 30, 180 or 365. Leave empty for one-time purchases.'),

            Code::make('Entitlements')
                ->json()
                ->rules(['json'])
                ->help('The entitlements granted by the product. For example: ["plus"] or ["pro"]')
                ->required(),

            Boolean::make('Is Active', 'is_active')
                ->sortable()
                ->help('Whether the product is available for sale and shown on the web store pages.'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $storeProduct = $this->resource;

        return $storeProduct->name . ' (ID: ' . $storeProduct->id . ')';
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
