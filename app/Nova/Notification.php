<?php

namespace App\Nova;

use App\Http\Resources\NotificationResource;
use Illuminate\Notifications\DatabaseNotification;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

class Notification extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = DatabaseNotification::class;

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
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        /** @var DatabaseNotification $notification */
        $notification = $this->resource;

        return [
            Text::make('UUID', 'id')
                ->hideFromIndex(),

            Text::make('Type')
                ->sortable(),

            Text::make('Body', function() use ($notification) { return NotificationResource::getNotificationString($notification); })
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
            <path fill="var(--sidebar-icon)" d="M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2zm0 10v6h14v-6h-2.38l-1.45 2.9a2 2 0 0 1-1.79 1.1h-2.76a2 2 0 0 1-1.8-1.1L7.39 13H5zm14-2V5H5v6h2.38a2 2 0 0 1 1.8 1.1l1.44 2.9h2.76l1.45-2.9a2 2 0 0 1 1.79-1.1H19z"/>
        </svg>
    ';
}
