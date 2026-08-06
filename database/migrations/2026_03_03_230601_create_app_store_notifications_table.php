<?php

use App\Models\AppStoreNotification;
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
        Schema::create(AppStoreNotification::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('notification_uuid')->unique();
            $table->string('notification_type');
            $table->string('subtype')->nullable();
            $table->string('original_transaction_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->json('payload');
            $table->timestamp('received_at');
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
        Schema::dropIfExists(AppStoreNotification::TABLE_NAME);
    }
};
