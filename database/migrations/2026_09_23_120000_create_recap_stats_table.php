<?php

use App\Models\RecapStat;
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
        Schema::create(RecapStat::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->year('year');
            $table->tinyInteger('month');
            $table->unsignedSmallInteger('stat');
            $table->bigInteger('value')->default(0);
            $table->nullableMorphs('model');
            $table->date('occurred_at')->nullable();
            $table->timestamps();
        });

        Schema::table(RecapStat::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['user_id', 'year', 'month', 'stat']);

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
        Schema::dropIfExists(RecapStat::TABLE_NAME);
    }
};
