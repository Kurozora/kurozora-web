<?php

use App\Models\Language;
use App\Models\Season;
use App\Models\SeasonTranslation;
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
        Schema::create(SeasonTranslation::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('season_id');
            $table->string('locale', 2);
            $table->string('title', 280);
            $table->text('synopsis')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(SeasonTranslation::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['season_id', 'locale']);

            // Set foreign key constraints
            $table->foreign('season_id')
                ->references('id')
                ->on(Season::TABLE_NAME)
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
        Schema::dropIfExists(SeasonTranslation::TABLE_NAME);
    }
};
