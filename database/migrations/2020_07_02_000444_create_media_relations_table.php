<?php

use App\Models\MediaRelation;
use App\Models\Relation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(MediaRelation::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('model_id');
            $table->string('model_type');
            $table->unsignedBigInteger('relation_id');
            $table->unsignedBigInteger('related_id');
            $table->string('related_type');
            $table->timestamps();
        });

        Schema::table(MediaRelation::TABLE_NAME, function (Blueprint $table) {
            // Set unique index constraints
            $table->unique(['model_id', 'model_type', 'relation_id', 'related_id', 'related_type'], 'model_relation_related_unique');

            // Set foreign key constraints
            $table->foreign('relation_id')->references('id')->on(Relation::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(MediaRelation::TABLE_NAME);
    }
}
