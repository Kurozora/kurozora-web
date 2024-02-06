<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffRole extends KModel
{
    use HasFactory,
        SoftDeletes;

    // Table name
    const string TABLE_NAME = 'staff_roles';
    protected $table = self::TABLE_NAME;
}
