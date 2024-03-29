<?php

use App\Models\Person;
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
        Schema::create(Person::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('mal_id')->unique()->nullable();
            $table->string('slug');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('family_name')->nullable();
            $table->string('given_name')->nullable();
            $table->json('alternative_names')->nullable();
            $table->text('about')->nullable();
            $table->string('short_description')->nullable();
            $table->unsignedTinyInteger('astrological_sign')->nullable();
            $table->json('website_urls')->nullable();
            $table->date('birthdate')->nullable();
            $table->date('deceased_date')->nullable();
            $table->unsignedBigInteger('rank_total')->default(0);
            $table->integer('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(Person::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('rank_total');
            $table->index('birthdate');
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Person::TABLE_NAME);
    }
};
