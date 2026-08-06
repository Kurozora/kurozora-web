<?php

use App\Models\StoreProduct;
use App\Models\User;
use App\Models\UserReceipt;
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
        Schema::create(UserReceipt::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('original_transaction_id');
            $table->string('product_id')->nullable();
            $table->string('subscription_group_id')->nullable();
            $table->boolean('is_subscribed')->default(false);
            $table->string('auto_renew_product_id')->nullable();
            $table->boolean('will_auto_renew')->default(false);
            $table->boolean('will_price_increase')->default(false);
            $table->tinyInteger('expiration_intent')->nullable();
            $table->dateTime('original_purchased_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('revoked_at')->nullable();
            $table->dateTime('grace_period_expires_date')->nullable();
            $table->timestamps();
        });

        Schema::table(UserReceipt::TABLE_NAME, function (Blueprint $table) {
            // Set index constraints
            $table->index('expires_at');

            // Set unique key constraints
            $table->unique('original_transaction_id');

            // Set foreign key constraints
            $table->foreign('user_id')
                ->references('uuid')
                ->on(User::TABLE_NAME)
                ->cascadeOnUpdate();
            $table->foreign('product_id')
                ->references('product_id')
                ->on(StoreProduct::TABLE_NAME)
                ->nullOnDelete()
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
        Schema::dropIfExists(UserReceipt::TABLE_NAME);
    }
};
