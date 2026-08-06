<?php

use App\Models\StoreProduct;
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
        Schema::create(StoreProduct::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->unique();
            $table->string('name');
            $table->string('platform');
            $table->tinyInteger('type');
            $table->string('subscription_group')->nullable();
            $table->bigInteger('price_usd_milliunits')->nullable();
            $table->integer('duration_days')->nullable();
            $table->json('entitlements');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(StoreProduct::TABLE_NAME);
    }
};
