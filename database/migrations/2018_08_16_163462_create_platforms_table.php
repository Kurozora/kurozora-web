<?php

use App\Models\Platform;
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
        Schema::create(Platform::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('original_name');
            $table->json('synonym_names')->nullable();
            $table->mediumText('about')->nullable();
            $table->unsignedTinyInteger('type');
            $table->unsignedTinyInteger('generation');
            $table->unsignedBigInteger('rank_total')->default(0);
            $table->integer('view_count')->default(0);
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Platform::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('rank_total');
            $table->index('started_at');
            $table->index('ended_at');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['slug']);
            $table->unique(['type', 'original_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Platform::TABLE_NAME);
    }
};
