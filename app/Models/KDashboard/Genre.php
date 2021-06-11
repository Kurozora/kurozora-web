<?php

namespace App\Models\KDashboard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'KDashboard';

    // Table name
    const TABLE_NAME = 'genre';
    protected $table = self::TABLE_NAME;

    protected $primaryKey = 'unique_id';
}
