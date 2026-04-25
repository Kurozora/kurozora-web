<?php

use App\Models\ReconciliationRow;
use App\Models\ReconciliationRun;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(ReconciliationRow::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_run_id')
                ->constrained(ReconciliationRun::TABLE_NAME)
                ->cascadeOnDelete();
            $table->string('source', 16);
            $table->uuid('user_id')->nullable();
            $table->string('original_transaction_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('product_id')->nullable();
            $table->string('notification_uuid')->nullable();
            $table->string('status', 24);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['reconciliation_run_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(ReconciliationRow::TABLE_NAME);
    }
};
