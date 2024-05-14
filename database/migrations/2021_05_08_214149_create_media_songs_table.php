<?php

use App\Models\MediaSong;
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
        Schema::create(MediaSong::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->unsignedBigInteger('song_id');
            $table->tinyInteger('type');
            $table->integer('position');
            $table->string('episodes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(MediaSong::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('position');
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['model_type', 'model_id', 'song_id', 'type']);

            // Set foreign key constraints
            $table->foreign('song_id')
                ->references('id')
                ->on(Song::TABLE_NAME)
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
        Schema::dropIfExists(MediaSong::TABLE_NAME);
    }
};
