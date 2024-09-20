<?php

use App\Models\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(Country::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 2);
            $table->string('iso_3166_3', 3);
            $table->timestamps();
        });

        Schema::table(Country::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Country::TABLE_NAME);
    }
};
