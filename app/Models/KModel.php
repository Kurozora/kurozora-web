<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Random\RandomException;

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
     * Get the user's preferred "tv rating".
     *
     * @return int
     */
    static function getTvRatingSettings(): int
    {
        return request()->tvRating();
    }

    /**
     * Efficient single-row `ORDER BY RAND()` replacement.
     *
     * Picks `id >= rand(1, max_id)` with a short retry budget, avoiding the
     * O(N log N) sort that `inRandomOrder()->first()` pays on large tables.
     * Preserves any scopes, wheres, or joins already on the builder by
     * cloning it for each probe.
     *
     * @param Builder $query
     *
     * @return static|null
     * @throws RandomException
     */
    public function scopeRandomFirst(Builder $query): ?Model
    {
        $column = $this->qualifyColumn($this->getKeyName());
        $maxId = (clone $query)->max($column);

        if ($maxId === null) {
            return null;
        }

        for ($i = 0; $i < 5; $i++) {
            $candidate = (clone $query)
                ->where($column, '>=', random_int(1, (int) $maxId))
                ->first();

            if ($candidate !== null) {
                return $candidate;
            }
        }

        return (clone $query)->first();
    }
}
