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
        Schema::create(User::TABLE_NAME, function (Blueprint $table) {
            $table->increments('id');
            $table->string('siwa_id')->nullable();
            $table->timestamps();

            $table->string('username', 50)->nullable();
            $table->string('email');
            $table->string('password')->nullable();
            $table->text('biography')->nullable();
            $table->rememberToken();
            $table->string('email_confirmation_id')->nullable();
            $table->timestamp('last_mal_import_at')->nullable();
            $table->boolean('username_change_available')->default(false);
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
