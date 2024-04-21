<?php

use App\Models\Episode;
use App\Models\User;
use App\Models\UserWatchedEpisode;
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
        Schema::create(UserWatchedEpisode::TABLE_NAME, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('episode_id');
            $table->unsignedTinyInteger('rewatch_count')->default(0);
            $table->timestamps();
        });

        Schema::table(UserWatchedEpisode::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['user_id', 'episode_id']);

            // Set foreign key constraints
            $table->foreign('user_id')
                ->references('id')
                ->on(User::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('episode_id')
                ->references('id')
                ->on(Episode::TABLE_NAME)
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
        Schema::dropIfExists(UserWatchedEpisode::TABLE_NAME);
    }
};
