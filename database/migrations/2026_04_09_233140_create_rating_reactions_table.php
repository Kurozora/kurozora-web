<?php

use App\Models\MediaRating;
use App\Models\RatingReaction;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(RatingReaction::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rating_id');
            $table->unsignedBigInteger('user_id');
            // helpful | not_helpful
            $table->string('type', 20);
            $table->timestamps();
        });

        Schema::table(RatingReaction::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('type');
            // Set unique key constraints
            $table->unique(['rating_id', 'user_id']);
            // Set foreign key constraints
            $table->foreign('rating_id')
                ->references('id')
                ->on(MediaRating::TABLE_NAME)
                ->cascadeOnDelete();
            $table->foreign('user_id')
                ->references('id')
                ->on(User::TABLE_NAME)
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(RatingReaction::TABLE_NAME);
    }
};
