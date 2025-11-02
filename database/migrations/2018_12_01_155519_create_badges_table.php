<?php

use App\Models\Badge;
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
        Schema::create(Badge::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable()->default(null);
            $table->string('text_color', 10)->default('#000000');
            $table->string('background_color', 10)->default('#FFFFFF');
            $table->boolean('is_unlockable')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Badge::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('deleted_at');

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
        Schema::dropIfExists(Badge::TABLE_NAME);
    }
};
