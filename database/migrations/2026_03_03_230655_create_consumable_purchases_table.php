<?php

use App\Models\ConsumablePurchase;
use App\Models\StoreProduct;
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
        Schema::create(ConsumablePurchase::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('product_id')->nullable();
            $table->string('transaction_id');
            $table->dateTime('purchased_at');
            $table->dateTime('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::table(ConsumablePurchase::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique('transaction_id');

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
        Schema::dropIfExists(ConsumablePurchase::TABLE_NAME);
    }
};
