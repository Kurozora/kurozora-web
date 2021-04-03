<?php

namespace Laravel\Nova\Tests\Fixtures;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class UserWithCustomFields extends UserResource
{
    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'user-with-custom-fields';
    }

    public function fieldsForIndex(NovaRequest $request)
    {
        return [
            Text::make('Index Name', 'name'),
        ];
    }

    public function fieldsForDetail(NovaRequest $request)
    {
        return [
            Text::make('Detail Name', 'name'),

            new Panel('Profiles', [
                Boolean::make('Restricted'),
                File::make('Avatar', 'avatar', null, function ($request, $model) {
                    return $request->avatar->storeAs('avatars', 'avatar.png');
                })->rules('required')->delete(function ($request) {
                    $_SERVER['__nova.fileDeleted'] = true;

                    return $_SERVER['__nova.fileDelete'] ?? null;
                })->prunable(),
            ]),
        ];
    }

    public function fieldsForUpdate(NovaRequest $request)
    {
        return [
            Text::make('Update Name', 'name'),
        ];
    }

    public function fieldsForCreate(NovaRequest $request)
    {
        return [
            Text::make('Create Name', 'name'),
            Text::make('Nickname', 'nickname'),
        ];
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(),
            Text::make('Weight')->onlyOnDetail(),

            $this->mergeWhen($_SERVER['nova.showDuplicateField'] ?? false, [
                Text::make('Index Name', 'name'),
            ]),

            $this->merge([
                Text::make('Name (Email)', function () {
                    return $this->name.' '.$this->email;
                })->onlyOnIndex(),
            ]),
        ];
    }
}
