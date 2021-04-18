<?php

use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForumRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(ForumReply::TABLE_NAME, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('love_reactant_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('thread_id');
            $table->string('ip_address', 45);
            $table->text('content');
            $table->timestamp('edited_at')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table(ForumReply::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('love_reactant_id')->references('id')->on('love_reactants');
            $table->foreign('user_id')->references('id')->on(User::TABLE_NAME)->onDelete('cascade');
            $table->foreign('thread_id')->references('id')->on(ForumThread::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(ForumReply::TABLE_NAME);
    }
}
