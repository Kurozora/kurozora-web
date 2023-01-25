<?php

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
            $table->string('web_order_line_item_id')->nullable();
            $table->string('offer_id')->nullable();
            $table->string('subscription_group_id')->nullable();
            $table->string('product_id');
            $table->boolean('is_subscribed');
            $table->boolean('will_auto_renew');
            $table->dateTime('original_purchased_at');
            $table->dateTime('purchased_at');
            $table->dateTime('expired_at')->nullable();
            $table->dateTime('upgraded_at')->nullable();
            $table->dateTime('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::table(UserReceipt::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['user_id', 'original_transaction_id']);

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
        Schema::dropIfExists(UserReceipt::TABLE_NAME);
    }
};
