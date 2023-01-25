<?php

use App\Models\MediaStudio;
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
        Schema::create(MediaStudio::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->uuidMorphs('model');
            $table->unsignedBigInteger('studio_id');
            $table->boolean('is_licensor');
            $table->boolean('is_producer');
            $table->boolean('is_studio');
            $table->boolean('is_publisher');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(MediaStudio::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['model_type', 'model_id', 'studio_id']);

            // Set foreign key constraints
            $table->foreign('studio_id')
                ->references('id')
                ->on('studios')
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
        Schema::dropIfExists(MediaStudio::TABLE_NAME);
    }
};
