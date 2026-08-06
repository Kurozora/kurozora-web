<?php

use App\Models\MediaTool;
use App\Models\Tool;
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
        Schema::create(MediaTool::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->unsignedBigInteger('tool_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(MediaTool::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['model_type', 'model_id', 'tool_id'], 'media_tool_unique');

            // Set foreign key constraints
            $table->foreign('tool_id')
                ->references('id')
                ->on(Tool::TABLE_NAME)
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
        Schema::dropIfExists(MediaTool::TABLE_NAME);
    }
};
