<?php

use App\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Language::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('code', 2);
            $table->timestamps();
        });

        Schema::table(Language::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Language::TABLE_NAME);
    }
}