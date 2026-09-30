<?php

use App\Models\Player;
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
        Schema::create(Player::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('original_name');
            $table->json('alternative_names')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Player::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('created_at');
            $table->index('updated_at');

            // Set unique key constraints
            $table->unique(['slug']);
            $table->unique(['original_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Player::TABLE_NAME);
    }
};
