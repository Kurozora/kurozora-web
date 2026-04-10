<?php

use App\Models\MediaTypeCategory;
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
        Schema::create(MediaTypeCategory::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('rating_category_id');
            $table->unsignedTinyInteger('display_order')->default(0)->comment("Controls the order in which categories are shown in the review form");
            $table->timestamps();

        });

        Schema::table(MediaTypeCategory::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('model_type');
            // Set unique key constraints
            $table->unique(['model_type', 'rating_category_id']);
            // Set foreign key constraints
            $table->foreignId('rating_category_id')
                ->constrained(RatingCategory::TABLE_NAME)
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
        Schema::dropIfExists(MediaTypeCategory::TABLE_NAME);
    }
};