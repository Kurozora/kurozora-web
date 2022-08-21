<?php

use App\Models\Studio;
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
        Schema::create(Studio::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('mal_id')->nullable();
            $table->string('slug');
            $table->unsignedTinyInteger('type');
            $table->string('name');
            $table->mediumText('about')->nullable();
            $table->mediumText('address')->nullable();
            $table->date('founded')->nullable();
            $table->json('website_urls')->nullable();
            $table->timestamps();
        });

        Schema::table(Studio::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['slug']);
            $table->unique(['mal_id', 'type', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Studio::TABLE_NAME);
    }
};
