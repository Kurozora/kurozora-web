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
            $table->morphs('taggable');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(MediaTag::TABLE_NAME, function(Blueprint $table) {
            // Set index key constraints
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['taggable_type', 'taggable_id', 'tag_id']);

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
