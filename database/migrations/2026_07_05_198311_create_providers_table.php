<?php

use App\Models\Provider;
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
        Schema::create(Provider::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('original_name');
            $table->json('alternative_names')->nullable();
            $table->string('url')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Provider::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index(['deleted_at', 'is_public']);
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
        Schema::dropIfExists(Provider::TABLE_NAME);
    }
};
