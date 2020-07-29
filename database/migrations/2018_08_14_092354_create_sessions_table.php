<?php

use App\Rules\ValidateAPNDeviceToken;
use App\Rules\ValidatePlatformVersion;
use App\Session;
use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Session::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('apn_device_token', ValidateAPNDeviceToken::TOKEN_LENGTH)->nullable()->unique();
            $table->string('secret', 128);

            // Platform information
            $table->string('platform')->nullable();
            $table->string('platform_version', ValidatePlatformVersion::MAX_VERSION_LENGTH)->nullable();
            $table->string('device_vendor')->nullable();
            $table->string('device_model')->nullable();

            // Location information
            $table->string('ip')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('country')->nullable();
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();

            $table->timestamp('expires_at')->useCurrent();
            $table->timestamp('last_validated_at')->useCurrent();
            $table->timestamps();
        });

        Schema::table(Session::TABLE_NAME, function (Blueprint $table) {
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
        Schema::dropIfExists(Session::TABLE_NAME);
    }
}
