<?php

use App\Models\StaffRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffRolesTable extends Migration
{
    public function up()
    {
        Schema::create(StaffRole::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::table(StaffRole::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists(StaffRole::TABLE_NAME);
    }
}
