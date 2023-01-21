<?php

use App\Models\Comment;
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
        Schema::create(Comment::TABLE_NAME, function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('comment_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->uuidMorphs('commentable');
            $table->text('content');
            $table->boolean('is_spoiler')->default(false);
            $table->boolean('is_nsfw')->default(false);
            $table->boolean('is_nsfl')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->unsignedInteger('replies_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('reports_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Comment::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('comment_id')->references('id')->on(Comment::TABLE_NAME)->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on(User::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Comment::TABLE_NAME);
    }
};
