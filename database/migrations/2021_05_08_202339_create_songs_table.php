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
            $table->bigIncrements('id');
            $table->unsignedInteger('am_id')->unique()->nullable();
            $table->unsignedInteger('mal_id')->unique()->nullable();
            $table->string('slug');
            $table->string('title');
            $table->string('artist', 500)->nullable();
            $table->timestamps();
        });

        Schema::table(Song::TABLE_NAME, function (Blueprint $table) {
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
