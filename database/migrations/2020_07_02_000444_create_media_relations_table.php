<?php

use App\Models\MediaRelation;
use App\Models\Relation;
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
        Schema::create(MediaRelation::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->unsignedBigInteger('relation_id');
            $table->morphs('related');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(MediaRelation::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['model_type', 'model_id', 'relation_id', 'related_type', 'related_id'], 'model_relation_related_unique');

            // Set foreign key constraints
            $table->foreign('relation_id')
                ->references('id')
                ->on(Relation::TABLE_NAME)
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
        Schema::dropIfExists(MediaRelation::TABLE_NAME);
    }
};
