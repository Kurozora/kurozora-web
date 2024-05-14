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
            $table->boolean('type')->default(VideoType::Default);
            $table->boolean('is_sub');
            $table->boolean('is_dub');
            $table->integer('order');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Video::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('order');
            $table->index('deleted_at');

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
