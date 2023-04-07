<?php

namespace App\Nova\Lenses;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Lenses\Lens;

class UnconfirmedUsers extends Lens
{
    /**
     * Get the query builder / paginator for the lens.
     *
     * @param LensRequest $request
     * @param  Builder  $query
     * @return mixed
     */
    public static function query(LensRequest $request, $query): mixed
    {
        return $request->withOrdering($request->withFilters(
            $query->select(self::columns())
                ->where('email_verified_at')
        ));
    }

    /**
     * Get the columns that should be selected.
     *
     * @return array
     */
    protected static function columns(): array
    {
        return [
            User::TABLE_NAME . '.id',
            User::TABLE_NAME . '.username',
            User::TABLE_NAME . '.email',
            User::TABLE_NAME . '.created_at'
        ];
    }

    /**
     * Get the fields available to the lens.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make('ID', 'id')->sortable(),

            Text::make('Username'),

            Text::make('Email Address', 'email'),

            Text::make('Is not verified since', 'created_at', function($value) {
                if ($value == null) {
                    return 'Unknown';
                } else {
                    return $value->diffForHumans();
                }
            }),
        ];
    }

    /**
     * Get the filters available for the lens.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available on the lens.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return parent::actions($request);
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'unconfirmed-users';
    }
}
