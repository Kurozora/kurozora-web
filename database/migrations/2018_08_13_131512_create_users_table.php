<?php

use App\Models\Language;
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
        Schema::create(User::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->unsignedBigInteger('love_reacter_id')->nullable();
            $table->string('siwa_id')->nullable();
            $table->string('language_id', 2)->default('en')->nullable();
            $table->string('slug');
            $table->string('username', 50)->nullable();
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('email_suspended')->default(false);
            $table->string('password')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->rememberToken();
            $table->text('biography')->nullable();
            $table->text('biography_html')->nullable();
            $table->text('biography_markdown')->nullable();
            $table->json('settings');
            $table->boolean('is_pro')->default(false);
            $table->boolean('is_subscribed')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->integer('view_count')->default(0);
            $table->timestamp('last_anime_import_at')->nullable();
            $table->timestamp('last_manga_import_at')->nullable();
            $table->timestamps();
        });

        Schema::table(User::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('language_id');

            // Set unique key constraints
            $table->unique(['slug']);

            // Set foreign key constraints
            $table->foreign('love_reacter_id')
                ->references('id')
                ->on('love_reacters')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('locale')
                ->references('code')
                ->on(Language::TABLE_NAME)
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
        Schema::dropIfExists(User::TABLE_NAME);
    }
};
