<?php

use App\Models\ExploreCategory;
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
        Schema::create(ExploreCategory::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('slug');
            $table->string('type');
            $table->string('size');
            $table->smallInteger('position');
            $table->boolean('is_global');
            $table->boolean('is_enabled');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(ExploreCategory::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('position');
            $table->index('is_enabled');
            $table->index('is_enabled');
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(ExploreCategory::TABLE_NAME);
    }
};
