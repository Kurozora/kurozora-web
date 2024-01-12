<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;

class Notification extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Notification::class;

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\Notification|null
     */
    public $resource;

    /**
     * Determine if the resource should be available for the given request.
     *
     * @param Request $request
     * @return bool
     */
    public static function authorizedToViewAny(Request $request): bool
    {
        return $request->user()?->can('viewNotification') ?? false;
    }

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
        'id', 'type'
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
        $notification = $this->resource;

        return [
            Heading::make('Identification'),

            Text::make('UUID', 'id')
                ->required()
                ->hideFromIndex(),

            Heading::make('Meta information'),

            Text::make('Type')
                ->required()
                ->sortable(),

            Text::make('Body', function() use ($notification) { return $notification->description; })
                ->readonly(),

            MorphTo::make('Receiver', 'notifiable')->types([
                User::class
            ])->searchable(),

            Boolean::make('Is read', function() { return $this->resource->read_at != null; })
                ->readonly()
                ->onlyOnIndex(),

            Code::make('Extra data', 'data')
                ->json()
                ->hideFromIndex(),

            DateTime::make('Read at')
                ->hideFromIndex(),

            DateTime::make('Created at')
                ->hideFromIndex(),

            DateTime::make('Updated at')
                ->hideFromIndex(),
        ];
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
            <path fill="var(--sidebar-icon)" d="M11.1111111,0 L88.8888889,0 C95.0253861,0 100,4.97461389 100,11.1111111 L100,88.8888889 C100,95.0253861 95.0253861,100 88.8888889,100 L11.1111111,100 C4.97461389,100 0,95.0253861 0,88.8888889 L0,11.1111111 C0,5 5,0 11.1111111,0 Z M11.1111111,55.5555556 L11.1111111,88.8888889 L88.8888889,88.8888889 L88.8888889,55.5555556 L75.6666667,55.5555556 L67.6111111,71.6666667 C65.7193213,75.4209588 61.870654,77.7860616 57.6666667,77.7777778 L42.3333333,77.7777778 C38.1093682,77.8071741 34.2340614,75.4389311 32.3333333,71.6666667 L24.3888889,55.5555556 L11.1111111,55.5555556 Z M88.8888889,44.4444444 L88.8888889,11.1111111 L11.1111111,11.1111111 L11.1111111,44.4444444 L24.3333333,44.4444444 C28.5572985,44.4150481 32.4326053,46.7832911 34.3333333,50.5555556 L42.3333333,66.6666667 L57.6666667,66.6666667 L65.7222222,50.5555556 C67.614012,46.8012634 71.4626794,44.4361606 75.6666667,44.4444444 L88.8888889,44.4444444 Z"/>
        </svg>
    ';
}
