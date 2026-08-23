<?php

use App\Enums\VideoType;
use App\Models\Language;
use App\Models\Video;
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
        Schema::create(Video::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->morphs('videoable');
            $table->unsignedBigInteger('language_id');
            $table->string('source');
            $table->string('code');
            $table->tinyInteger('type')->default(VideoType::Default);
            $table->boolean('is_sub');
            $table->boolean('is_dub');
            $table->integer('order');
            $table->integer('view_count')->default(0);
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Video::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index(['deleted_at', 'order']);
            $table->index(['deleted_at', 'published_at']);

            // Set foreign key constraints
            $table->foreign('language_id')
                ->references('id')
                ->on(Language::TABLE_NAME)
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
        Schema::dropIfExists(Video::TABLE_NAME);
    }
};
