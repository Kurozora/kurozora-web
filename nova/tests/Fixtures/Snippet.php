<?php

namespace Laravel\Nova\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Snippet extends Model
{
    protected $casts = [
        'code' => 'array',
    ];

    public $timestamps = false;
}
