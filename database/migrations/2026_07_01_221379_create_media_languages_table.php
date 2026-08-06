<?php

use App\Models\Language;
use App\Models\MediaLanguage;
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
        Schema::create(MediaLanguage::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->unsignedBigInteger('language_id');
            $table->unsignedTinyInteger('type');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(MediaLanguage::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['model_type', 'model_id', 'language_id', 'type'], 'media_language_unique');

            // Set foreign key constraints
            $table->foreign('language_id')
                ->references('id')
                ->on(Language::TABLE_NAME)
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
        Schema::dropIfExists(MediaLanguage::TABLE_NAME);
    }
};
