<?php

use App\Models\Song;
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
        Schema::create(Song::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('amazon_id')->unique()->nullable();
            $table->unsignedInteger('am_id')->unique()->nullable();
            $table->unsignedInteger('deezer_id')->unique()->nullable();
            $table->unsignedInteger('mal_id')->unique()->nullable();
            $table->string('spotify_id')->unique()->nullable();
            $table->string('youtube_id')->unique()->nullable();
            $table->string('slug', 280);
            $table->string('title', 280);
            $table->string('artist', 500)->nullable();
            $table->unsignedBigInteger('rank_total')->default(0);
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Song::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('rank_total');
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['slug']);
            $table->unique(['title', 'artist']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Song::TABLE_NAME);
    }
};
