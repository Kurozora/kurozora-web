<?php

use App\Models\TwoFactorChallenge;
use App\Models\User;
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
        Schema::create(TwoFactorChallenge::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('token_hash', 64);
            $table->unsignedTinyInteger('attempts_used')->default(0);
            $table->json('platform_data')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::table(TwoFactorChallenge::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index(['token_hash', 'expires_at']);

            // Set unique key constraints
            $table->unique('token_hash');

            // Set foreign key constraints
            $table->foreign('user_id')
                ->references('uuid')
                ->on(User::TABLE_NAME)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(TwoFactorChallenge::TABLE_NAME);
    }
};
