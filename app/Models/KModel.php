<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KModel extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();
    }

    /**
     * Drop every global scope except the one hiding deleted records.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function withoutGlobalScopesExceptSoftDeletes(Builder $query): Builder
    {
        $scopes = array_keys($this->getGlobalScopes());

        return $query->withoutGlobalScopes(array_values(array_diff($scopes, [SoftDeletingScope::class])));
    }

    /**
     * Get the user's preferred "tv rating".
     *
     * @return int
     */
    static function getTvRatingSettings(): int
    {
        return request()->tvRating();
    }
}
