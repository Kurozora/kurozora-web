<?php

use App\Models\Achievement;
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
        Schema::create(Achievement::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable()->default(null);
            $table->boolean('is_unlockable')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Achievement::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Achievement::TABLE_NAME);
    }
};
