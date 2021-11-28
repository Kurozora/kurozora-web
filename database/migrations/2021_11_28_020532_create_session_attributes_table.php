<?php

use App\Models\SessionAttribute;
use App\Rules\ValidateAPNDeviceToken;
use App\Rules\ValidatePlatformVersion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(SessionAttribute::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('model_id');
            $table->string('model_type');
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
            $table->timestamps();
        });

        Schema::table(SessionAttribute::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['model_id', 'model_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(SessionAttribute::TABLE_NAME);
    }
}
