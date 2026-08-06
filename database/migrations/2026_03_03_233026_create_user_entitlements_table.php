<?php

use App\Models\User;
use App\Models\UserEntitlement;
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
        Schema::create(UserEntitlement::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('key'); // e.g., 'pro', 'beta_features', 'subscription'
            $table->string('source_type'); // 'apple_receipt', 'admin_manual', 'stripe'
            $table->string('source_id')->nullable(); // the original_transaction_id or subscription_id
            $table->timestamp('granted_at');
            $table->timestamp('expires_at')->nullable(); // null = lifetime
            $table->timestamps();
        });

        Schema::table(UserEntitlement::TABLE_NAME, function (Blueprint $table) {
            // Set index constraints
            $table->index('expires_at');

            // Set unique key constraints
            $table->unique(['user_id', 'key']);

            // Set foreign key constraints
            $table->foreign('user_id')
                ->references('uuid')
                ->on(User::TABLE_NAME)
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
        Schema::dropIfExists(UserEntitlement::TABLE_NAME);
    }
};
