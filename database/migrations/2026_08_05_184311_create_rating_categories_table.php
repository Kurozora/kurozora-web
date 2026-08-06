<?php

use App\Models\RatingCategory;
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
        Schema::create(RatingCategory::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->string('slug');
            $table->string('name');
            $table->string('description')->nullable();
            $table->double('weight')->default(1.0);
            $table->unsignedTinyInteger('display_order')->default(0);
            $table->timestamps();
        });

        Schema::table(RatingCategory::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('model_type');

            // Set unique key constraints
            $table->unique(['model_type', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(RatingCategory::TABLE_NAME);
    }
};
