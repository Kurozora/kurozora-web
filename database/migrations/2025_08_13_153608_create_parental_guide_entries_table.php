<?php

use App\Models\ParentalGuideEntry;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(ParentalGuideEntry::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->morphs('model');
            $table->unsignedTinyInteger('category');
            $table->unsignedTinyInteger('rating');
            $table->unsignedTinyInteger('frequency')->nullable();
            $table->unsignedTinyInteger('depiction')->nullable();
            $table->text('reason')->nullable();
            $table->boolean('is_spoiler')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
        });

        Schema::table(ParentalGuideEntry::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index(['model_type', 'model_id', 'category']);
            $table->index(['model_type', 'model_id', 'category', 'is_hidden'], 'parental_guide_model_type_model_id_category_is_hidden_index');

            // Set foreign key constraints
            $table->foreign('user_id')
                ->references('id')
                ->on(User::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(ParentalGuideEntry::TABLE_NAME);
    }
};
