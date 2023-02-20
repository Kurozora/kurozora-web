<?php

use App\Models\Cache;
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
        Schema::create(Cache::TABLE_NAME, function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::table(Cache::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index(['expiration']);
        });

        Schema::table('cache_locks', function (Blueprint $table) {
            // Set index key constraints
            $table->index(['owner']);
            $table->index(['expiration']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Cache::TABLE_NAME);
        Schema::dropIfExists('cache_locks');
    }
};
