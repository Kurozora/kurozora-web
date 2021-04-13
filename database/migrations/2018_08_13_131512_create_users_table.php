<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('love_reacter_id')->nullable();
            $table->string('siwa_id')->nullable();
            $table->string('username', 50)->nullable();
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->text('biography')->nullable();
            $table->rememberToken();
            $table->string('email_confirmation_id')->nullable();
            $table->timestamp('last_mal_import_at')->nullable();
            $table->boolean('username_change_available')->default(false);
            $table->timestamps();
        });

        Schema::table(User::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('love_reacter_id')->references('id')->on('love_reacters');
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
