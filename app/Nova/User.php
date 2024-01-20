<?php

namespace App\Nova;

use App\Enums\MediaCollection;
use App\Nova\Filters\PremiumStatus;
use App\Nova\Filters\UserRole;
use App\Nova\Lenses\UnconfirmedUsers;
use App\Rules\ValidateEmail;
use App\Rules\ValidatePassword;
use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Exception;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaColorField\Color;
use Ramsey\Uuid\Uuid;
use Vyuldashev\NovaPermission\RoleBooleanGroup;

class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\User::class;

    /**
     * Determine if the resource should be available for the given request.
     *
     * @param Request $request
     *
     * @return bool
     */
    public static function authorizedToViewAny(Request $request): bool
    {
        return $request->user()?->hasRole('admin') ?? false;
    }

    /**
     * Determine if the current user can view the given resource.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function authorizedToView(Request $request): bool
    {
        return $request->user()?->hasRole('admin') ?? false;
    }

    /**
     * Determine if the current user can update the given resource.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function authorizedToUpdate(Request $request): bool
    {
        return $request->user()?->hasRole('admin') ?? false;
    }

    /**
     * Determine if the current user can delete the given resource.
     *
     * @param Request $request
     * @return bool
     */
    public function authorizedToDelete(Request $request): bool
    {
        return $request->user()?->hasRole('admin') ?? false;
    }

    /**
     * Determine if the current user can impersonate the given resource.
     *
     * @param  NovaRequest  $request
     * @return bool
     */
    public function authorizedToImpersonate(Request $request): bool
    {
        return $request->user()?->hasRole('superAdmin') ?? false;
    }

    /**
     * Determine if the current user can replicate the given resource.
     *
     * @param Request $request
     * @return bool
     */
    public function authorizedToReplicate(Request $request): bool
    {
        return $request->user()?->hasRole('admin') ?? false;
    }

    /**
     * The underlying model resource instance.
     *
     * @var \App\Models\User|null
     */
    public $resource;

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
     * @param Request $request
     *
     * @return array
     * @throws Exception
     */
    public function fields(Request $request): array
    {
        return [
            Heading::make('Identification'),

            ID::make()->sortable(),

            Heading::make('Media'),

            Avatar::make('Profile')
                ->thumbnail(function () {
                    return $this->resource->getFirstMediaFullUrl(MediaCollection::Profile()) ?? asset('images/static/placeholders/user_profile.webp');
                })->preview(function () {
                    return $this->resource->getFirstMediaFullUrl(MediaCollection::Profile()) ?? asset('images/static/placeholders/user_profile.webp');
                })
                ->rounded()
                ->deletable(false)
                ->disableDownload()
                ->readonly()
                ->onlyOnPreview(),

            Images::make('Profile')
                ->showStatistics()
                ->setFileName(function ($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function ($originalFilename, $model) {
                    return $this->resource->username;
                })
                ->customPropertiesFields([
                    Heading::make('Colors (automatically generated if empty)'),

                    Color::make('Background Color')
                        ->slider()
                        ->help('The average background color of the image.'),

                    Color::make('Text Color 1')
                        ->slider()
                        ->help('The primary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 2')
                        ->slider()
                        ->help('The secondary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 3')
                        ->slider()
                        ->help('The tertiary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 4')
                        ->slider()
                        ->help('The final post-tertiary text color that may be used if the background color is displayed.'),

                    Heading::make('Dimensions (automatically generated if empty)'),

                    Number::make('Width')
                        ->help('The maximum width available for the image.'),

                    Number::make('Height')
                        ->help('The maximum height available for the image.'),
                ]),

            Images::make('Banner')
                ->hideFromIndex()
                ->showStatistics()
                ->setFileName(function ($originalFilename, $extension, $model) {
                    return Uuid::uuid4() . '.' . $extension;
                })
                ->setName(function ($originalFilename, $model) {
                    return $this->resource->username;
                })
                ->customPropertiesFields([
                    Heading::make('Colors (automatically generated if empty)'),

                    Color::make('Background Color')
                        ->slider()
                        ->help('The average background color of the image.'),

                    Color::make('Text Color 1')
                        ->slider()
                        ->help('The primary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 2')
                        ->slider()
                        ->help('The secondary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 3')
                        ->slider()
                        ->help('The tertiary text color that may be used if the background color is displayed.'),

                    Color::make('Text Color 4')
                        ->slider()
                        ->help('The final post-tertiary text color that may be used if the background color is displayed.'),

                    Heading::make('Dimensions (automatically generated if empty)'),

                    Number::make('Width')
                        ->help('The maximum width available for the image.'),

                    Number::make('Height')
                        ->help('The maximum height available for the image.'),
                ]),

            Heading::make('Meta information'),

            Text::make('Name', 'username')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Email')
                ->canSee(function (Request $request) {
                    return $request->user()->hasRole('admin');
                })
                ->sortable()
                ->rules('required', new ValidateEmail),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules(['required', new ValidatePassword])
                ->updateRules(['nullable', new ValidatePassword]),

            Textarea::make('Biography'),

            BelongsTo::make('Language'),

            Boolean::make('Is Developer')
                ->help('Indicates whether the user is an active Kurozora developer.'),

            Boolean::make('Is Staff')
                ->help('Indicates whether the user is a Kurozora staff.'),

            Boolean::make('Is Early Supporter')
                ->help('Indicates whether the user was one of the early 100 Kurozora users.'),

            Boolean::make('Is Pro')
                ->help('Indicates whether the user has bought Kurozora Pro.'),

            Boolean::make('Is Subscribed')
                ->help('Indicates whether the user is subscribed to Kurozora+ services.'),

            Boolean::make('Is Verified')
                ->help('Indicates whether the user is verified, because they’re notable in animators, voice actors, entertainment studios, or another designated category.'),

            Date::make('Subscribed At')
                ->hideFromIndex()
                ->help('The date time the user subscribed at.'),

            Date::make('Anime Imported At')
                ->hideFromIndex()
                ->help('The date at which the user last imported an anime export file. The cooldown is <strong>' . config('import.cooldown_in_days') . '</strong> day(s).'),

            Date::make('Manga Imported At')
                ->hideFromIndex()
                ->help('The date at which the user last imported a manga export file. The cooldown is <strong>' . config('import.cooldown_in_days') . '</strong> day(s).'),

            // Roles and permissions
            RoleBooleanGroup::make('Roles')
                ->hideFromIndex(),

            // Display roles on index
            Text::make('Roles', function () {
                return $this->displayRolesForIndex();
            })
                ->asHtml()
                ->readonly()
                ->onlyOnIndex(),

            MorphMany::make('Notifications'),

            HasMany::make('Favorites', 'favorites', UserFavorite::class),

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
    public function title(): string
    {
        $user = $this->resource;

        return $user->username . ' (ID: ' . $user->id . ')';
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     *
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
     *
     * @return array
     */
    public function filters(Request $request): array
    {
        return [
            new UserRole,
            new PremiumStatus,
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function lenses(Request $request): array
    {
        return [
            new UnconfirmedUsers
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
     *
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
     * Renders all the user's roles for the index view.
     *
     * @return ?string
     */
    private function displayRolesForIndex(): ?string
    {
        // Get the role names of the user
        $user = $this->resource;

        $roles = $user->getRoleNames();

        // Return null when there are no roles to properly render an "empty" cell
        if ($roles->isEmpty()) {
            return null;
        }

        // Join all roles together and create the string
        $roleString = '';

        foreach ($roles as $role) {
            $roleString .= '<span class="inline-block align-middle mr-1 pt-1 pr-2 pb-1 pl-2 rounded" style="background-color: #465161; color: #fff;">' . $role . '</span>';
        }

        return $roleString;
    }

    /**
     * The icon of the resource.
     *
     * @var string
     */
    public static string $icon = '
        <svg class="sidebar-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <path fill="var(--sidebar-icon)" d="M50.1922388,50.0540199 C63.0431053,50.0540199 73.5183142,38.6068957 73.5183142,24.6760743 C73.5183142,10.8531821 63.0971805,0 50.1922388,0 C37.3952263,0 26.8660529,11.069151 26.8660529,24.7840035 C26.9200174,38.6609709 37.3412618,50.0540199 50.1922388,50.0540199 Z M50.1922388,41.9007251 C42.3087669,41.9007251 35.5593251,34.3412618 35.5593251,24.7840035 C35.5593251,15.3887495 42.2008378,8.15340535 50.1922388,8.15340535 C58.2374939,8.15340535 64.825042,15.2808203 64.825042,24.6760743 C64.825042,34.2333326 58.1295647,41.9007251 50.1922388,41.9007251 Z M16.2289502,100 L84.1014523,100 C93.0647685,100 97.3304025,97.3002343 97.3304025,91.3607145 C97.3304025,77.2138468 79.4578452,56.7494971 50.1922388,56.7494971 C20.8725574,56.7494971 3,77.2138468 3,91.3607145 C3,97.3002343 7.26563397,100 16.2289502,100 Z M13.6910672,91.8466721 C12.2872143,91.8466721 11.6932722,91.4686989 11.6932722,90.3347905 C11.6932722,81.4795029 25.4081248,64.9027919 50.1922388,64.9027919 C74.9221672,64.9027919 88.6371303,81.4795029 88.6371303,90.3347905 C88.6371303,91.4686989 88.0971528,91.8466721 86.6932999,91.8466721 L13.6910672,91.8466721 Z"/>
        </svg>
    ';
}
