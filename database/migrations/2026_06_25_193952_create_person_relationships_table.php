<?php

use App\Models\Person;
use App\Models\PersonRelationship;
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
        Schema::create(PersonRelationship::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('person_id');
            $table->unsignedBigInteger('related_person_id');
            $table->unsignedTinyInteger('type');
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(PersonRelationship::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index(['person_id', 'deleted_at']);

            // Set unique key constraints
            $table->unique(['person_id', 'related_person_id', 'type']);

            // Set foreign key constraints
            $table->foreign('person_id')
                ->references('id')
                ->on(Person::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('related_person_id')
                ->references('id')
                ->on(Person::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(PersonRelationship::TABLE_NAME);
    }
};
