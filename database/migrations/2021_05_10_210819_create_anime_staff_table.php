<?php

use App\Models\Anime;
use App\Models\AnimeStaff;
use App\Models\Person;
use App\Models\StaffRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnimeStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(AnimeStaff::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('anime_id');
            $table->unsignedBigInteger('person_id');
            $table->unsignedBigInteger('staff_role_id')->nullable();
            $table->timestamps();
        });

        Schema::table(AnimeStaff::TABLE_NAME, function (Blueprint $table) {
            // Set foreign key constraints
            $table->foreign('anime_id')->references('id')->on(Anime::TABLE_NAME)->onDelete('cascade');
            $table->foreign('person_id')->references('id')->on(Person::TABLE_NAME)->onDelete('cascade');
            $table->foreign('staff_role_id')->references('id')->on(StaffRole::TABLE_NAME)->onDelete('set null');

            // Set unique key constraints
            $table->unique(['anime_id', 'person_id', 'staff_role_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(AnimeStaff::TABLE_NAME);
    }
}
