<?php

use App\Models\User;
use App\Models\UserReminder;
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
        Schema::create(UserReminder::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->morphs('remindable');
            $table->timestamps();
        });

        Schema::table(UserReminder::TABLE_NAME, function (Blueprint $table) {
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
        Schema::dropIfExists(UserReminder::TABLE_NAME);
    }
};
