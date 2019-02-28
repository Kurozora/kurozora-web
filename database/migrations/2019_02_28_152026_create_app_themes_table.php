<?php

use App\AppTheme;
use App\Enums\iOSUIKit;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(AppTheme::TABLE_NAME, function (Blueprint $table) {
            $table->increments('id');

            $table->string('name');

            $table->string('statusbar_style')->default(iOSUIKit::StatusBarStyleDefault);
            $table->string('background_color');
            $table->string('text_color');
            $table->string('tint_color');
            $table->string('bar_tint_color');
            $table->string('bar_title_text_color');

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
        Schema::dropIfExists(AppTheme::TABLE_NAME);
    }
}
