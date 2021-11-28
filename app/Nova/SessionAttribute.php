<?php

namespace App\Nova;

use App\Rules\ValidateAPNDeviceToken;
use App\Rules\ValidatePlatformName;
use App\Rules\ValidatePlatformVersion;
use App\Rules\ValidateVendorName;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class SessionAttribute extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\SessionAttribute::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\SessionAttribute|null
     */
    public $resource;

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
        'id', 'platform', 'platform_version', 'device_vendor', 'device_model'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Sessions';

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            MorphTo::make('Model')
                ->types([
                    Session::class,
                    PersonalAccessToken::class
                ])
                ->searchable()
                ->sortable(),

            Text::make('Model Type')
                ->onlyOnIndex()
                ->onlyOnDetail()
                ->sortable(),

            Text::make('Platform', function () {
                return $this->resource->humanReadablePlatform();
            })
                ->readonly()
                ->onlyOnIndex(),

            Boolean::make('Notifications', function() {
                return $this->resource->apn_device_token !== null;
            })
                ->readonly()
                ->onlyOnIndex(),

            Heading::make('Platform information'),

            Text::make('Platform')
                ->rules('required', new ValidatePlatformName)
                ->hideFromIndex(),

            Text::make('Platform version')
                ->rules('required', new ValidatePlatformVersion)
                ->hideFromIndex(),

            Text::make('Device vendor')
                ->rules('required', new ValidateVendorName)
                ->hideFromIndex(),

            Text::make('Device model')
                ->rules('required', 'max:50')
                ->hideFromIndex(),

            Heading::make('Location'),

            Text::make('IP Address', 'ip_address')
                ->rules('max:45')
                ->hideFromIndex(),

            Text::make('City')
                ->rules('max:255')
                ->hideFromIndex(),

            Text::make('Region')
                ->rules('max:255')
                ->hideFromIndex(),

            Text::make('Country')
                ->rules('max:255')
                ->hideFromIndex(),

            Number::make('Latitude (coordinates)', 'latitude')
                ->step(0.001)
                ->hideFromIndex(),

            Number::make('Longitude (coordinates)', 'longitude')
                ->step(0.001)
                ->hideFromIndex(),

            Text::make('', function () {
                $enabled = $this->resource->latitude !== null && $this->resource->longitude !== null;
                $mapsURL = 'https://www.google.com/maps/search/?api=1&query=' . $this->resource->latitude .',' . $this->resource->longitude;

                return $enabled ? '
                    <a href="' . $mapsURL . '" target="_blank" class="btn btn-default btn-primary">Open location in Google Maps</a>
                ' : '<strong>Google Maps link could not be generated at this time.</strong>';
            })
                ->asHtml()
                ->readonly()
                ->onlyOnDetail(),

            Heading::make('Apple Push Notifications'),

            Text::make('APN device token', 'apn_device_token')
                ->rules('max:' . ValidateAPNDeviceToken::TOKEN_LENGTH)
                ->hideFromIndex(),
        ];
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

    /**
     * Determine if the given resource is authorizable.
     *
     * @return bool
     */
    public static function authorizable(): bool
    {
        return false;
    }
}
