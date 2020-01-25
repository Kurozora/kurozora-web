<?php

namespace App\Nova;

use App\Nova\Filters\UserRole;
use App\Nova\Lenses\UnconfirmedUsers;
use App\Rules\ValidateEmail;
use App\Rules\ValidatePassword;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Textarea;
use Vyuldashev\NovaPermission\PermissionBooleanGroup;
use Vyuldashev\NovaPermission\RoleBooleanGroup;

class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\\User';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'username';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'username', 'email',
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
        return [
            ID::make()->sortable(),

            Images::make('Avatar', 'avatar'),
//                ->conversionOnIndexView('thumb'),

            Images::make('Banner image', 'banner')
                ->hideFromIndex(),
//                ->conversionOnIndexView('thumb'),

            Text::make('Name', 'username')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Email')
                ->sortable()
                ->rules('required', new ValidateEmail(false)),

            Password::make('Password')
                ->onlyOnForms()
                ->rules(new ValidatePassword(false)),

            Textarea::make('Biography'),

            Date::make('Last MAL Import date', 'last_mal_import_at')
                ->help('The date at which the user last imported a MAL export file. The cooldown is <strong>' . config('mal-import.cooldown_in_days') . '</strong> day(s).')
                ->hideFromIndex(),

            // Roles and permissions
            RoleBooleanGroup::make('Roles')->hideFromIndex(),
            PermissionBooleanGroup::make('Permissions')->hideFromIndex(),

            // Display roles on index
            Text::make('Roles', function() { return $this->displayRolesForIndex(); })
                ->asHtml()
                ->readonly()
                ->onlyOnIndex(),

            HasMany::make('Forum Threads', 'threads'),

            HasMany::make('User Notifications', 'notifications'),

            BelongsToMany::make('Moderating Anime', 'moderatingAnime', Anime::class)
                ->fields(function() {
                    return [
                        DateTime::make('Moderating since', 'created_at')
                            ->rules('required')
                    ];
                })
                ->searchable(),

            BelongsToMany::make('Badges')
                ->searchable(),

            HasMany::make('Sessions'),
        ];
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        return $this->username . ' (ID: ' . $this->id . ')';
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new UserRole
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [
            new UnconfirmedUsers
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
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
            <path fill="var(--sidebar-icon)" d="M12 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-2a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm9 11a1 1 0 0 1-2 0v-2a3 3 0 0 0-3-3H8a3 3 0 0 0-3 3v2a1 1 0 0 1-2 0v-2a5 5 0 0 1 5-5h8a5 5 0 0 1 5 5v2z"/>
        </svg>
    ';

    /**
     * Renders all the user's roles for the index view.
     *
     * @return string
     */
    private function displayRolesForIndex() {
        // Get the role names of the user
        /** @var \App\User $user */
        $user = $this->resource;

        $roles = $user->getRoleNames();

        // Return null when there are no roles to properly render an "empty" cell
        if($roles->isEmpty()) return null;

        // Join all roles together and create the string
        $roleString = '';

        foreach($roles as $role)
            $roleString .= '<span class="py-1 px-2 mr-1 inline-block rounded align-middle" style="background-color: #465161; color: #fff;">' . $role . '</span>';

        return $roleString;
    }
}
