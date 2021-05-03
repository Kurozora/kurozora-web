<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffRole extends Model
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'staff_roles';
    protected $table = self::TABLE_NAME;
}
