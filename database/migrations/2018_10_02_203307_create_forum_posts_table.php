<?php

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
        Schema::create('forum_post', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->timestamp('edited_at')->nullable()->default(null);

            $table->integer('section_id')->unsigned();
            $table->foreign('section_id')->references('id')->on('forum_section')->onDelete('cascade');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');

            $table->integer('parent_post')->unsigned()->nullable()->default(null);
            $table->foreign('parent_post')->references('id')->on('forum_post')->onDelete('cascade');

            $table->string('ip');
            $table->integer('score')->default(0);
            $table->string('title')->nullable()->default(null);
            $table->text('content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forum_post');
    }
}
