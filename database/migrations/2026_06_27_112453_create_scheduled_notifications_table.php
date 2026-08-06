<?php

use App\Enums\ScheduledNotificationStatus;
use App\Models\ScheduledNotification;
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
        Schema::create(ScheduledNotification::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('type');
            $table->morphs('subject');
            $table->dateTime('scheduled_at');
            $table->dateTime('dispatched_at')->nullable();
            $table->unsignedTinyInteger('status')->default(ScheduledNotificationStatus::Pending);
            $table->timestamps();
        });

        Schema::table(ScheduledNotification::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index(['status', 'scheduled_at']);

            // Set unique key constraints
            $table->unique(['type', 'subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(ScheduledNotification::TABLE_NAME);
    }
};
