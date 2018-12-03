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
            $table->increments('id');
            $table->timestamps();
            $table->timestamp('edited_at')->nullable()->default(null);

            $table->integer('section_id')->unsigned();
            $table->foreign('section_id')->references('id')->on(ForumSection::TABLE_NAME)->onDelete('cascade');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on(User::TABLE_NAME)->onDelete('cascade');

            $table->string('ip');
            $table->integer('score')->default(0);
            $table->string('title')->nullable()->default(null);
            $table->text('content');
            $table->boolean('locked')->default(false);
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
