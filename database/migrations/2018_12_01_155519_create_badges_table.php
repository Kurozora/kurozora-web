<?php

use App\Models\Badge;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->bigIncrements('id');
            $table->string('text')->nullable()->default(null);
            $table->string('textColor', 10)->default('#000000');
            $table->string('backgroundColor', 10)->default('#FFFFFF');
            $table->string('description')->nullable()->default(null);
            $table->timestamps();
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
