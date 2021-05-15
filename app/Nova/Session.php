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
    public static string $model = \App\Models\Session::class;

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
    public function fields(Request $request): array
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

    /**
     * The icon of the resource.
     *
     * @var string
     */
    public static string $icon = '
        <svg class="sidebar-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M0.714162538,95.0242264 C0.961385964,97.7609339 2.79791318,99.9644646 5.5879648,100 L27.4140611,100 C30.2041345,99.9644573 32.2525737,97.7609412 32.2878706,95.0242336 L32.252523,84.0419336 L48.1806264,84.0775418 C50.9000841,84.1130627 52.9837624,81.8739383 53.054501,79.1017099 L53.089798,64.2098807 C67.463909,69.2922752 80.8491987,66.8399529 90.2436166,57.3859451 C103.275647,44.2712507 103.24035,23.0530691 90.2082473,9.93830192 C77.070181,-3.28310063 56.127073,-3.31862149 43.0243761,9.86718741 C33.559292,19.3923097 30.5926108,33.3244934 35.3957468,45.7639275 L2.0562429,79.3149079 C0.890719701,80.4878241 -0.0981016746,82.1937716 0.00778922537,84.1485835 L0.714162538,95.0242264 Z M8.76657238,91.896571 L8.73127541,84.0419482 L45.1787207,47.3633124 C38.7509839,37.127409 40.1636582,24.1192772 48.6751456,15.5538004 C58.563938,5.60228224 74.5627221,5.6378031 84.5221808,15.6604358 C94.4463425,25.6475476 94.4462701,41.7122203 84.5574777,51.6637385 C76.1165843,60.1581735 63.0845536,61.6153294 52.1009043,54.8269875 L44.9667942,62.00635 L44.9314972,75.9740546 L30.0628526,75.8674338 L24.2001855,81.7672884 L24.2708229,91.7899502 L8.76657238,91.896571 Z M66.3337551,33.9642328 C69.865477,37.5183568 75.5869128,37.5183568 79.1539317,33.9287119 C82.6856536,30.3745879 82.6856536,24.6168604 79.1539317,21.0627363 C75.5515435,17.4374978 69.9008463,17.4374978 66.3337551,21.0272155 C62.7667363,24.6168604 62.7666639,30.3745151 66.3337551,33.9642328 Z"/>
        </svg>
    ';
}
