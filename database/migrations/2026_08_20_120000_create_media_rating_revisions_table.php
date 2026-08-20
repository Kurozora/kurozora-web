<?php

use App\Models\MediaRating;
use App\Models\MediaRatingRevision;
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
        Schema::create(MediaRatingRevision::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rating_id');
            $table->double('rating')->default(MediaRating::MAX_RATING_VALUE);
            $table->text('description');
            $table->boolean('is_spoiler')->default(false);
            $table->unsignedTinyInteger('recommendation')->nullable();
            $table->unsignedInteger('progress')->nullable();
            $table->timestamp('written_at');
        });

        Schema::table(MediaRatingRevision::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index(['rating_id', 'written_at']);

            // Set foreign key constraints
            $table->foreign('rating_id')
                ->references('id')
                ->on(MediaRating::TABLE_NAME)
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
        Schema::dropIfExists(MediaRatingRevision::TABLE_NAME);
    }
};
