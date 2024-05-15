<?php

use App\Models\Recap;
use App\Models\User;
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
        Schema::create(Recap::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->year('year');
            $table->string('type');
            $table->unsignedSmallInteger('total_type_count')->default(0);
            $table->unsignedBigInteger('total_parts_count')->default(0);
            $table->unsignedBigInteger('total_parts_duration')->default(0);
            $table->decimal('top_percentile', 5)->default(0.00);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Recap::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['type', 'user_id', 'year']);

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
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Recap::TABLE_NAME);
    }
};
