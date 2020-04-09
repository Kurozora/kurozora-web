<?php

use App\FeedMessage;
use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feed_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on(User::TABLE_NAME)->onDelete('cascade');

            $table->bigInteger('parent_feed_message_id')->unsigned()->nullable();
            $table->foreign('parent_feed_message_id')->references('id')->on(FeedMessage::TABLE_NAME)->onDelete('cascade');

            $table->string('body', 240);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feed_messages');
    }
}
