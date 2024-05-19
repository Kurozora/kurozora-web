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
            $table->string('slug', 280);
            $table->json('nicknames')->nullable();
            $table->string('debut')->nullable();
            $table->unsignedTinyInteger('status')->default(CharacterStatus::Unknown);
            $table->string('blood_type')->nullable();
            $table->string('favorite_food')->nullable();
            $table->decimal('height', 32)->nullable();
            $table->decimal('weight', 32)->nullable();
            $table->decimal('bust')->nullable();
            $table->decimal('waist')->nullable();
            $table->decimal('hip')->nullable();
            $table->decimal('age', 32)->nullable();
            $table->unsignedTinyInteger('birth_day')->nullable();
            $table->unsignedTinyInteger('birth_month')->nullable();
            $table->unsignedTinyInteger('astrological_sign')->nullable();
            $table->unsignedBigInteger('rank_total')->default(0);
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Character::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('rank_total');
            $table->index('birth_day');
            $table->index('birth_month');
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
        Schema::dropIfExists(Character::TABLE_NAME);
    }
};
