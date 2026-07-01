<?php

use App\Models\Franchise;
use App\Models\MediaFranchise;
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
        Schema::create(MediaFranchise::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->unsignedBigInteger('franchise_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(MediaFranchise::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['model_type', 'model_id', 'franchise_id'], 'media_franchise_unique');

            // Set foreign key constraints
            $table->foreign('franchise_id')
                ->references('id')
                ->on(Franchise::TABLE_NAME)
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
        Schema::dropIfExists(MediaFranchise::TABLE_NAME);
    }
};
