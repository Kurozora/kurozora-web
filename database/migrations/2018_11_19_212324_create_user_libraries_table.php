<?php

use App\Models\User;
use App\Models\UserLibrary;
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
        Schema::create(UserLibrary::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->uuidMorphs('trackable');
            $table->tinyInteger('status');
            $table->unsignedTinyInteger('rewatch_count')->default(0);
            $table->boolean('is_hidden')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::table(UserLibrary::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('user_id');
            $table->index('is_hidden');

            // Set unique key constraints
            $table->unique(['trackable_type', 'trackable_id', 'user_id']);

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
        Schema::dropIfExists(UserLibrary::TABLE_NAME);
    }
};
