<?php

use App\Models\Anime;
use App\Models\User;
use App\Models\UserReminderAnime;
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
        Schema::create(UserReminderAnime::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unsigned();
            $table->unsignedBigInteger('anime_id')->unsigned();
            $table->timestamps();
        });

        Schema::table(UserReminderAnime::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('user_id')
                ->references('id')
                ->on(User::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('anime_id')
                ->references('id')
                ->on(Anime::TABLE_NAME)
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
        Schema::dropIfExists(UserReminderAnime::TABLE_NAME);
    }
};
