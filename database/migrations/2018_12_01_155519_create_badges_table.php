<?php

use App\Badge;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBadgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Badge::TABLE_NAME, function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('text')->nullable()->default(null);
            $table->string('textColor', 10)->default('#FFFFFF');
            $table->string('backgroundColor', 10)->default('#FFFFFF');
            $table->string('description')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Badge::TABLE_NAME);
    }
}
