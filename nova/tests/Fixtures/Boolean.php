<?php

namespace Laravel\Nova\Tests\Fixtures;

use Laravel\Nova\Actions\Actionable;
use Illuminate\Database\Eloquent\Model;

class Boolean extends Model
{
    use Actionable;

    /**
     * The attributes that are guarded.
     *
     * @var array
     */
    protected $guarded = [];
}
