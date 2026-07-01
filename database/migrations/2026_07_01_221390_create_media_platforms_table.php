<?php

use App\Models\MediaPlatform;
use App\Models\Platform;
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
        Schema::create(MediaPlatform::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->unsignedBigInteger('platform_id');
            $table->string('region')->nullable();
            $table->string('release_status')->nullable();
            $table->date('released_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(MediaPlatform::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index(['deleted_at', 'released_at']);

            // Set unique key constraints
            $table->unique(['model_type', 'model_id', 'platform_id', 'region'], 'media_platform_unique');

            // Set foreign key constraints
            $table->foreign('platform_id')
                ->references('id')
                ->on(Platform::TABLE_NAME)
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
        Schema::dropIfExists(MediaPlatform::TABLE_NAME);
    }
};
