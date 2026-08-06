<?php

use App\Models\StoreProduct;
use App\Models\User;
use App\Models\UserReceiptTransaction;
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
        Schema::create(UserReceiptTransaction::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable();
            $table->string('transaction_id');
            $table->string('original_transaction_id');
            $table->string('product_id')->nullable();
            $table->string('web_order_line_item_id')->nullable();
            $table->string('offer_id')->nullable();
            $table->string('offer_type')->nullable();
            $table->string('offer_period')->nullable();
            $table->string('offer_discount_type')->nullable();
            $table->string('currency', 3)->nullable();
            $table->bigInteger('price_milliunits')->nullable();
            $table->bigInteger('price_usd_milliunits')->nullable();
            $table->integer('quantity')->nullable();
            $table->boolean('is_trial_period')->default(false);
            $table->boolean('is_in_intro_offer_period')->default(false);
            $table->boolean('is_upgraded')->default(false);
            $table->dateTime('purchased_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('revoked_at')->nullable();
            $table->integer('revocation_reason')->nullable();
            $table->timestamps();
        });

        Schema::table(UserReceiptTransaction::TABLE_NAME, function (Blueprint $table) {
            // Set index constraints
            $table->index('user_id');
            $table->index('original_transaction_id');
            $table->index('web_order_line_item_id');

            // Set unique key constraints
            $table->unique('transaction_id');

            // Set foreign key constraints
            $table->foreign('user_id')
                ->references('uuid')
                ->on(User::TABLE_NAME)
                ->nullOnDelete()
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
        Schema::dropIfExists(UserReceiptTransaction::TABLE_NAME);
    }
};
