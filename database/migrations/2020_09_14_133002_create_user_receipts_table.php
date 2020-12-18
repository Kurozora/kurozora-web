<?php

use App\Models\User;
use App\Models\UserReceipt;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(UserReceipt::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('original_transaction_id');
            $table->string('web_order_line_item_id');
            $table->dateTime('latest_expires_date');
            $table->longText('latest_receipt_data');
            $table->boolean('is_subscribed');
            $table->boolean('will_auto_renew');
            $table->dateTime('upgrade_date')->nullable();
            $table->dateTime('cancellation_date')->nullable();
            $table->string('subscription_product_id');
            $table->timestamps();
        });

        Schema::table(UserReceipt::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['user_id', 'original_transaction_id']);

            // Set foreign key constraints
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
        Schema::dropIfExists(UserReceipt::TABLE_NAME);
    }
}
