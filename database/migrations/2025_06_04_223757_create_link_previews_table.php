<?php

use App\Models\LinkPreview;
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
        Schema::create(LinkPreview::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->text('url');
            $table->string('handler')->nullable();
            $table->smallInteger('type');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->text('media_url')->nullable();
            $table->text('embed_html')->nullable();
            $table->string('provider')->nullable();
            $table->timestamp('fetched_at');
            $table->timestamps();
        });

        Schema::table(LinkPreview::TABLE_NAME, function (Blueprint $table) {
            $table->index('handler');
            $table->index('type');
            $table->index('fetched_at');
        });

        DB::statement('ALTER TABLE ' . LinkPreview::TABLE_NAME . ' ADD UNIQUE link_previews_url_unique (url(255))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(LinkPreview::TABLE_NAME);
    }
};
