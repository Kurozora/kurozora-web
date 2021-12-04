<?php

use App\Models\Studio;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Studio::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('mal_id')->nullable();
            $table->string('slug');
            $table->string('type');
            $table->string('name');
            $table->mediumText('about')->nullable();
            $table->mediumText('address')->nullable();
            $table->date('founded')->nullable();
            $table->json('website_urls')->nullable();
            $table->timestamps();
        });

        Schema::table(Studio::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['slug']);
            $table->unique(['mal_id', 'type', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Studio::TABLE_NAME);
    }
}
