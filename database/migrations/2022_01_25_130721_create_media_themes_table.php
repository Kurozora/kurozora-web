<?php

use App\Models\MediaTheme;
use App\Models\Theme;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(MediaTheme::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('theme_id');
            $table->timestamps();
        });

        Schema::table(MediaTheme::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['model_type', 'model_id', 'theme_id']);

            // Set foreign key constraints
            $table->foreign('theme_id')->references('id')->on(Theme::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(MediaTheme::TABLE_NAME);
    }
}
