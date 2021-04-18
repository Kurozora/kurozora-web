<?php

use App\Rules\ValidateAPNDeviceToken;
use App\Rules\ValidatePlatformVersion;
use App\Models\Session;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->string('session_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('apn_device_token', ValidateAPNDeviceToken::TOKEN_LENGTH)->nullable()->index();
            $table->string('secret', 128);

            // Platform information
            $table->string('platform')->nullable();
            $table->string('platform_version', ValidatePlatformVersion::MAX_VERSION_LENGTH)->nullable();
            $table->string('device_vendor')->nullable();
            $table->string('device_model')->nullable();

            // Location information
            $table->string('ip_address', 45)->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('country')->nullable();
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();

            // Timestamps
            $table->timestamp('expires_at')->useCurrent();
            $table->timestamp('last_activity_at')->useCurrent()->index();
            $table->timestamps();
        });

        Schema::table(Session::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('user_id')->references('id')->on(User::TABLE_NAME)->onDelete('cascade');

            // Set unique key constraints
            $table->unique(['session_id', 'secret', 'apn_device_token']);
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
