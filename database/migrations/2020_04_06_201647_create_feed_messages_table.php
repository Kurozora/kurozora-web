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
        Schema::create(FeedMessage::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('love_reactant_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('parent_feed_message_id')->nullable();
            $table->string('body', FeedMessage::MAX_BODY_LENGTH);
            $table->boolean('is_nsfw')->default(false);
            $table->boolean('is_spoiler')->default(false);
            $table->timestamps();
        });

        Schema::table(FeedMessage::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('love_reactant_id')->references('id')->on('love_reactants');
            $table->foreign('user_id')->references('id')->on(User::TABLE_NAME)->onDelete('cascade');
            $table->foreign('parent_feed_message_id')->references('id')->on(FeedMessage::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(FeedMessage::TABLE_NAME);
    }
}
