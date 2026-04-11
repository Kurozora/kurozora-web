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
            $table->string('slug')->unique()->comment("Machine-readable slug, e.g. story, gameplay, visuals");
            $table->string('name')->comment("Human-readable label, e.g. Story, Gameplay, Visuals");
            $table->string('description')->nullable()->comment("Optional description shown as helper text in the UI");
            $table->float('weight')->default(1.0);
            $table->timestamps();
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