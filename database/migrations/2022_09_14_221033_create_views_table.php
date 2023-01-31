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
            $table->ulid('id')->primary();
            $table->uuidMorphs('viewable');
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(View::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('viewed_at');
            $table->index('deleted_at');
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
