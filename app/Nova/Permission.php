<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Nova;
use Vyuldashev\NovaPermission\Permission as BasePermission;
use Vyuldashev\NovaPermission\RoleBooleanGroup;

class Permission extends BasePermission
{
    /**
     * The underlying model resource instance.
     *
     * @var \Spatie\Permission\Models\Permission|null
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
        return $request->user()->can('viewPermission');
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name', 'display_name'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        $guardOptions = collect(config('auth.guards'))->mapWithKeys(function ($value, $key) {
            return [$key => $key];
        });

        $userResource = Nova::resourceForModel(getModelForGuard($this->guard_name));

        return [
            ID::make()->sortable(),

            Text::make(__('nova-permission-tool::permissions.name'), 'name')
                ->rules(['required', 'string', 'max:255'])
                ->creationRules('unique:'.config('permission.table_names.permissions'))
                ->updateRules('unique:'.config('permission.table_names.permissions').',name,{{resourceId}}')
                ->hideFromIndex(),

            Text::make(__('Name'), 'display_name')
                ->onlyOnIndex(),

            Text::make(__('Display Name'), 'display_name')
                ->rules(['required', 'string', 'max:255'])
                ->hideFromIndex(),

            Text::make(__('Group'), 'group')
                ->rules(['nullable', 'string', 'max:255'])
                ->sortable(),

            Text::make(__('nova-permission-tool::permissions.display_name'), function () {
                return __('nova-permission-tool::permissions.display_names.'.$this->name);
            })->canSee(function () {
                return is_array(__('nova-permission-tool::permissions.display_names'));
            }),

            Select::make(__('nova-permission-tool::permissions.guard_name'), 'guard_name')
                ->options($guardOptions->toArray())
                ->rules(['required', Rule::in($guardOptions)]),

            DateTime::make(__('nova-permission-tool::permissions.created_at'), 'created_at')->exceptOnForms(),
            DateTime::make(__('nova-permission-tool::permissions.updated_at'), 'updated_at')->exceptOnForms(),

            RoleBooleanGroup::make(__('nova-permission-tool::permissions.roles'), 'roles'),

            MorphToMany::make($userResource::label(), 'users', $userResource)
                ->searchable()
                ->singularLabel($userResource::singularLabel()),
        ];
    }
}
