<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TvRating extends Model
{
    use HasFactory;

    /**
     * The model's table name.
     *
     * @var string
     */
    const TABLE_NAME = 'tv_ratings';

    /**
     * The model's table name.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rating',
        'description',
    ];
}
