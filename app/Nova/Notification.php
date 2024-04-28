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
}
