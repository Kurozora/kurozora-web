<?php

use App\Models\SitemapShard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(SitemapShard::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('source_table', 64);
            $table->unsignedInteger('shard_index');
            $table->unsignedBigInteger('id_range_start');
            $table->unsignedBigInteger('id_range_end');
            $table->unsignedInteger('row_count')->default(0);
            $table->timestamp('max_updated_at')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['source_table', 'shard_index']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(SitemapShard::TABLE_NAME);
    }
};
