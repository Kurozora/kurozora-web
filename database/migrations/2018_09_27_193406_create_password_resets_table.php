<?php

use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePasswordResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(PasswordReset::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('ip');
            $table->string('token', PasswordReset::TOKEN_LENGTH);
            $table->timestamps();
        });

        Schema::table(PasswordReset::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('user_id')->references('id')->on(User::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(PasswordReset::TABLE_NAME);
    }
}
