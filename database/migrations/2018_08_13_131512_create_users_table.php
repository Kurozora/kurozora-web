<?php

use App\Enums\UserRole;
use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(User::TABLE_NAME, function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('username', 50);
            $table->string('email');
            $table->string('password');
            $table->tinyInteger('role')->default(UserRole::Normal);
            $table->text('biography')->nullable();
            $table->rememberToken();
            $table->string('email_confirmation_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(User::TABLE_NAME);
    }
}
