<?php

use App\Models\MediaStat;
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
        Schema::create(MediaStat::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->unsignedBigInteger('model_count')->default(0);
            $table->unsignedBigInteger('planning_count')->default(0);
            $table->unsignedBigInteger('in_progress_count')->default(0);
            $table->unsignedBigInteger('completed_count')->default(0);
            $table->unsignedBigInteger('on_hold_count')->default(0);
            $table->unsignedBigInteger('dropped_count')->default(0);
            $table->unsignedBigInteger('interested_count')->default(0);
            $table->unsignedBigInteger('ignored_count')->default(0);
            $table->unsignedBigInteger('rating_1')->default(0);
            $table->unsignedBigInteger('rating_2')->default(0);
            $table->unsignedBigInteger('rating_3')->default(0);
            $table->unsignedBigInteger('rating_4')->default(0);
            $table->unsignedBigInteger('rating_5')->default(0);
            $table->unsignedBigInteger('rating_6')->default(0);
            $table->unsignedBigInteger('rating_7')->default(0);
            $table->unsignedBigInteger('rating_8')->default(0);
            $table->unsignedBigInteger('rating_9')->default(0);
            $table->unsignedBigInteger('rating_10')->default(0);
            $table->double('rating_average')->default(0.0);
            $table->unsignedBigInteger('rating_count')->default(0);
            $table->unsignedBigInteger('rank_global')->default(0);
            $table->unsignedBigInteger('rank_total')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(MediaStat::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('deleted_at');
            $table->index(['rating_average', 'rating_count']);

            // Set unique key constraints
            $table->unique(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(MediaStat::TABLE_NAME);
    }
};
