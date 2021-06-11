<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Language::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->unique();
            $table->string('language');
            $table->timestamps();
        });

        Schema::table(Language::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['id']);
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
