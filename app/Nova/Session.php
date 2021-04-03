<?php

namespace App\Nova;

use App\Rules\ValidateAPNDeviceToken;
use App\Rules\ValidatePlatformName;
use App\Rules\ValidatePlatformVersion;
use App\Rules\ValidateVendorName;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class Session extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Session';

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
    public static $group = 'Users';

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        /** @var \App\Models\Session $session */
        $session = $this->resource;

        return [
            ID::make()->sortable(),

            BelongsTo::make('User')
                ->searchable(),

            Text::make('Secret Token', function() {
                return '••••••';
            })
                ->onlyOnDetail()
                ->readonly(),

            Text::make('Secret Token', 'secret')
                ->rules('required', 'max:128')
                ->onlyOnForms(),

            Text::make('Platform', function () use($session) {
                return $session->humanReadablePlatform();
            })
                ->readonly()
                ->onlyOnIndex(),

            Boolean::make('Notifications', function() use($session) {
                return $session->apn_device_token !== null;
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

            Text::make('IP Address', 'ip')
                ->rules('max:255')
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

            Text::make('', function () use($session) {
                $enabled = $session->latitude !== null && $session->longitude !== null;
                $mapsURL = 'https://www.google.com/maps/search/?api=1&query=' . $session->latitude .',' . $session->longitude;

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
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    /**
     * Determine if the given resource is authorizable.
     *
     * @return bool
     */
    public static function authorizable()
    {
        return false;
    }

    /**
     * The icon of the resource.
     *
     * @var string
     */
    public static $icon = '
        <svg class="sidebar-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path fill="var(--sidebar-icon)" d="M11.85 17.56a1.5 1.5 0 0 1-1.06.44H10v.5c0 .83-.67 1.5-1.5 1.5H8v.5c0 .83-.67 1.5-1.5 1.5H4a2 2 0 0 1-2-2v-2.59A2 2 0 0 1 2.59 16l5.56-5.56A7.03 7.03 0 0 1 15 2a7 7 0 1 1-1.44 13.85l-1.7 1.71zm1.12-3.95l.58.18a5 5 0 1 0-3.34-3.34l.18.58L4 17.4V20h2v-.5c0-.83.67-1.5 1.5-1.5H8v-.5c0-.83.67-1.5 1.5-1.5h1.09l2.38-2.39zM18 9a1 1 0 0 1-2 0 1 1 0 0 0-1-1 1 1 0 0 1 0-2 3 3 0 0 1 3 3z"/>
        </svg>
    ';
}
