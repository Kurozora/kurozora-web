<?php

namespace Database\Migrations\KDashboard;

use App\Models\KDashboard\MediaRelated;
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
        Schema::create(MediaRelated::TABLE_NAME, function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement()->unique();
            $table->unsignedBigInteger('media_id');
            $table->unsignedBigInteger('related_id');
            $table->unsignedBigInteger('related_type_id');
            $table->string('media_type');
            $table->string('related_type');
            $table->timestamps();
        });

        Schema::table(MediaRelated::TABLE_NAME, function (Blueprint $table) {
            $table->primary(['media_id', 'media_type', 'related_type_id', 'related_id', 'related_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(MediaRelated::TABLE_NAME);
    }
};
