<?php

use App\Enums\RatingStyle;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(UserSetting::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('scrobble_threshold')->default(90);
            $table->boolean('discord_rich_presence_enabled')->default(true);
            $table->unsignedTinyInteger('discord_presence_image')->default(0);
            $table->unsignedTinyInteger('discord_activity_name')->default(0);
            $table->unsignedTinyInteger('rating_style')->default(RatingStyle::Standard);
            $table->timestamps();
        });

        Schema::table(UserSetting::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['user_id']);

            // Set foreign key constraints
            $table->foreign('user_id')
                ->references('id')
                ->on(User::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(UserSetting::TABLE_NAME);
    }
};
