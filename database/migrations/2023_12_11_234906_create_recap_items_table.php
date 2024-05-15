<?php

use App\Models\Recap;
use App\Models\RecapItem;
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
        Schema::create(RecapItem::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recap_id');
            $table->morphs('model');
            $table->integer('position');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(RecapItem::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('position');
            $table->index('deleted_at');

            // Set foreign key constraints
            $table->foreign('recap_id')
                ->references('id')
                ->on(Recap::TABLE_NAME)
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
        Schema::dropIfExists(RecapItem::TABLE_NAME);
    }
};
