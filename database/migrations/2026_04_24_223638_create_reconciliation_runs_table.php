<?php

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
        Schema::create(ReconciliationRun::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->uuid('run_uuid')->unique();
            $table->string('source', 16);
            $table->string('environment', 16)->nullable();
            $table->string('user_id_filter')->nullable();
            $table->dateTime('since')->nullable();
            $table->dateTime('until')->nullable();

            // History counters
            $table->unsignedInteger('users_total')->default(0);
            $table->unsignedInteger('users_with_anchors')->default(0);
            $table->unsignedInteger('users_skipped')->default(0);
            $table->unsignedInteger('users_errored')->default(0);
            $table->unsignedInteger('apple_transactions')->default(0);
            $table->unsignedInteger('local_present')->default(0);
            $table->unsignedInteger('local_missing')->default(0);
            $table->unsignedInteger('local_orphan')->default(0);

            // Notification counters
            $table->unsignedInteger('notifications_total')->default(0);
            $table->unsignedInteger('notifications_present')->default(0);
            $table->unsignedInteger('notifications_missing')->default(0);

            // API usage
            $table->unsignedInteger('api_calls')->default(0);
            $table->unsignedInteger('api_retries')->default(0);
            $table->unsignedInteger('rate_limit_hits')->default(0);

            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(ReconciliationRun::TABLE_NAME);
    }
};
