<?php

use App\Models\Language;
use App\Models\Platform;
use App\Models\PlatformTranslation;
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
        Schema::create(PlatformTranslation::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id');
            $table->string('locale', 2);
            $table->string('name');
            $table->text('about')->nullable();
            $table->string('tagline')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(PlatformTranslation::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['platform_id', 'locale']);

            // Set foreign key constraints
            $table->foreign('platform_id')
                ->references('id')
                ->on(Platform::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('locale')
                ->references('code')
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
        Schema::dropIfExists(PlatformTranslation::TABLE_NAME);
    }
};
