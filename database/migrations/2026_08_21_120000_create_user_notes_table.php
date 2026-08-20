<?php

use App\Models\User;
use App\Models\UserNote;
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
        Schema::create(UserNote::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->morphs('noteable');
            $table->text('body');
            $table->timestamps();
        });

        Schema::table(UserNote::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index(['user_id', 'noteable_type', 'updated_at', 'id']);

            // Set unique key constraints
            $table->unique(['user_id', 'noteable_type', 'noteable_id'], 'user_notes_user_noteable_unique');

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
        Schema::dropIfExists(UserNote::TABLE_NAME);
    }
};
