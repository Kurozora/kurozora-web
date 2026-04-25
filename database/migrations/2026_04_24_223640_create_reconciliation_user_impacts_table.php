<?php

use App\Models\ReconciliationUserImpact;
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
        Schema::create(ReconciliationUserImpact::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_run_id');
            $table->uuid('user_id');
            $table->unsignedInteger('missing_transactions')->default(0);
            $table->boolean('before_pro')->default(false);
            $table->boolean('before_plus')->default(false);
            $table->boolean('before_is_pro_flag')->default(false);
            $table->boolean('before_is_subscribed_flag')->default(false);
            $table->boolean('after_pro')->default(false);
            $table->boolean('after_plus')->default(false);
            $table->json('before_entitlements')->nullable();
            $table->json('after_entitlements')->nullable();
            $table->string('error')->nullable();

            $table->dateTime('applied_at')->nullable();
            $table->unsignedInteger('applied_count')->default(0);
            $table->boolean('applied_pro')->default(false);
            $table->boolean('applied_plus')->default(false);
            $table->string('applied_error')->nullable();

            $table->timestamps();
        });

        Schema::table(ReconciliationUserImpact::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->unique(['reconciliation_run_id', 'user_id']);

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
        Schema::dropIfExists(ReconciliationUserImpact::TABLE_NAME);
    }
};
