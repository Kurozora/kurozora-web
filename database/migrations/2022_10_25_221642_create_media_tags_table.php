<?php

use App\Models\MediaTag;
use App\Models\Tag;
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
        Schema::create(MediaTag::TABLE_NAME, function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tag_id');
            $table->uuidMorphs('taggable');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(MediaTag::TABLE_NAME, function(Blueprint $table) {
            // Set unique key constraints
            $table->unique(['tag_id', 'taggable_id', 'taggable_type']);

            // Set foreign key constraints
            $table->foreign('tag_id')->references('id')
                ->on(Tag::TABLE_NAME)
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
        Schema::dropIfExists(MediaTag::TABLE_NAME);
    }
};
