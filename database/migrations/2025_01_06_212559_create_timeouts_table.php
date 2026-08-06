<?php

use App\Models\Timeout;
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
        Schema::create(Timeout::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('issued_by_id')->nullable();
            $table->unsignedTinyInteger('reason_key');
            $table->text('note');
            $table->boolean('is_permanent')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedBigInteger('revoked_by_id')->nullable();
            $table->timestamp('expiry_notified_at')->nullable();
            $table->timestamps();
        });

        Schema::table(Timeout::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index(['user_id', 'revoked_at', 'expires_at']);

            // Set foreign key constraints
            $table->foreign('user_id')
                ->references('id')
                ->on(User::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('issued_by_id')
                ->references('id')
                ->on(User::TABLE_NAME)
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('revoked_by_id')
                ->references('id')
                ->on(User::TABLE_NAME)
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
        Schema::dropIfExists(Timeout::TABLE_NAME);
    }
};
