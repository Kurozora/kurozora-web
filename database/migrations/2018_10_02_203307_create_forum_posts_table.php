<?php

use App\ForumThread;
use App\ForumSection;
use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(ForumThread::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('user_id');
            $table->string('ip');
            $table->string('title')->nullable()->default(null);
            $table->text('content');
            $table->boolean('locked')->default(false);
            $table->timestamp('edited_at')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table(ForumThread::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('section_id')->references('id')->on(ForumSection::TABLE_NAME)->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on(User::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(ForumThread::TABLE_NAME);
    }
}
