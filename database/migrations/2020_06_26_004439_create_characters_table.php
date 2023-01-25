<?php

use App\Enums\CharacterStatus;
use App\Models\Character;
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
        Schema::create(Character::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('mal_id')->unique()->nullable();
            $table->string('slug');
            $table->json('nicknames')->nullable();
            $table->string('debut')->nullable();
            $table->unsignedTinyInteger('status')->default(CharacterStatus::Unknown);
            $table->string('blood_type')->nullable();
            $table->string('favorite_food')->nullable();
            $table->unsignedDecimal('height', 32)->nullable();
            $table->unsignedDecimal('weight', 32)->nullable();
            $table->unsignedDecimal('bust')->nullable();
            $table->unsignedDecimal('waist')->nullable();
            $table->unsignedDecimal('hip')->nullable();
            $table->unsignedDecimal('age', 32)->nullable();
            $table->unsignedTinyInteger('birth_day')->nullable();
            $table->unsignedTinyInteger('birth_month')->nullable();
            $table->unsignedTinyInteger('astrological_sign')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Character::TABLE_NAME, function (Blueprint $table) {
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
        Schema::dropIfExists(Character::TABLE_NAME);
    }
};
