<?php

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
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('username', 50);
            $table->string('email');
            $table->string('password');
            $table->tinyInteger('role')->default(User::USER_ROLE_NORMAL);
            $table->string('avatar')->nullable();
            $table->string('banner')->nullable();
            $table->text('biography')->nullable();
            $table->string('email_confirmation_id')->nullable();
            $table->integer('follower_count')->default(0);
            $table->integer('following_count')->default(0);
            $table->integer('reputation_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
