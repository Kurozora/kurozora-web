<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffRole extends KModel
{
    use HasFactory;

    // Table name
    const string TABLE_NAME = 'staff_roles';
    protected $table = self::TABLE_NAME;
}
