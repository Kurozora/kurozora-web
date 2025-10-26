<?php

use App\Models\View;
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
        Schema::create(View::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->morphs('viewable');
            $table->string('ip_address');
            $table->timestamps();
        });

        Schema::table(View::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index(['viewable_id', 'viewable_type', 'ip_address', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(View::TABLE_NAME);
    }
};
