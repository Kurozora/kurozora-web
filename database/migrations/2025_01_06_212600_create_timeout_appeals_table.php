<?php

use App\Models\Timeout;
use App\Models\TimeoutAppeal;
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
        Schema::create(TimeoutAppeal::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('timeout_id');
            $table->text('message');
            $table->timestamps();
        });

        Schema::table(TimeoutAppeal::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['timeout_id']);

            // Set foreign key constraints
            $table->foreign('timeout_id')
                ->references('id')
                ->on(Timeout::TABLE_NAME)
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
        Schema::dropIfExists(TimeoutAppeal::TABLE_NAME);
    }
};
