<?php

use App\Models\PendingScrobble;
use App\Models\User;
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
        Schema::create(PendingScrobble::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('mal_id');
            $table->integer('season')->nullable();
            $table->unsignedInteger('number');
            $table->boolean('is_absolute')->default(false);
            $table->timestamp('watched_at');
            $table->timestamps();
        });

        Schema::table(PendingScrobble::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('mal_id');

            // Set unique key constraints
            $table->unique(['user_id', 'mal_id', 'season', 'number', 'is_absolute'], 'pending_scrobbles_identity_unique');

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
        Schema::dropIfExists(PendingScrobble::TABLE_NAME);
    }
};
