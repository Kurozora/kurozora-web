<?php

use App\Models\FeedMessage;
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
        Schema::create(FeedMessage::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('love_reactant_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('parent_feed_message_id')->nullable();
            $table->text('content');
            $table->text('content_html');
            $table->text('content_markdown');
            $table->text('last_link')->nullable();
            $table->boolean('is_nsfw')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_reply')->default(false);
            $table->boolean('is_reshare')->default(false);
            $table->boolean('is_spoiler')->default(false);
            $table->timestamps();
        });

        Schema::table(FeedMessage::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('love_reactant_id')
                ->references('id')
                ->on('love_reactants')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('user_id')
                ->references('id')
                ->on(User::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('parent_feed_message_id')
                ->references('id')
                ->on(FeedMessage::TABLE_NAME)
                ->cascadeOnDelete()
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
        Schema::dropIfExists(FeedMessage::TABLE_NAME);
    }
};
