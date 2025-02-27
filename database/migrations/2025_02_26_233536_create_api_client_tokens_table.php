<?php

use App\Models\APIClientToken;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(APIClientToken::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->uuid('key_id');
            $table->uuid('user_id');
            $table->string('identifier');
            $table->string('description');
            $table->text('token');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(APIClientToken::TABLE_NAME, function (Blueprint $table) {
            // Set unique key constraints
            $table->unique(['identifier']);
            $table->unique(['identifier', 'user_id']);

            // Set foreign key constraints
            $table->foreign('user_id')
                ->references('uuid')
                ->on(User::TABLE_NAME)
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(APIClientToken::TABLE_NAME);
    }
};
