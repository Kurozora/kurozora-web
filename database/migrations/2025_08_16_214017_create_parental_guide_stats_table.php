<?php

use App\Enums\ParentalGuideCategory;
use App\Models\ParentalGuideStat;
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
        Schema::create(ParentalGuideStat::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->morphs('model');

            $sexAndNudityColumn = ParentalGuideCategory::SexAndNudity()->columnName;
            $table->unsignedInteger($sexAndNudityColumn . '_rating_none')->default(0);
            $table->unsignedInteger($sexAndNudityColumn . '_rating_mild')->default(0);
            $table->unsignedInteger($sexAndNudityColumn . '_rating_moderate')->default(0);
            $table->unsignedInteger($sexAndNudityColumn . '_rating_severe')->default(0);
            $table->double($sexAndNudityColumn . '_average')->default(0.0);
            $table->unsignedInteger($sexAndNudityColumn . '_count')->default(0);
            $table->unsignedInteger($sexAndNudityColumn . '_freq_brief')->default(0);
            $table->unsignedInteger($sexAndNudityColumn . '_freq_occasional')->default(0);
            $table->unsignedInteger($sexAndNudityColumn . '_freq_frequent')->default(0);
            $table->unsignedInteger($sexAndNudityColumn . '_dep_implied')->default(0);
            $table->unsignedInteger($sexAndNudityColumn . '_dep_shown')->default(0);
            $table->unsignedInteger($sexAndNudityColumn . '_dep_graphic')->default(0);

            $violenceAndGoreColumn = ParentalGuideCategory::ViolenceAndGore()->columnName;
            $table->unsignedInteger($violenceAndGoreColumn . '_rating_none')->default(0);
            $table->unsignedInteger($violenceAndGoreColumn . '_rating_mild')->default(0);
            $table->unsignedInteger($violenceAndGoreColumn . '_rating_moderate')->default(0);
            $table->unsignedInteger($violenceAndGoreColumn . '_rating_severe')->default(0);
            $table->double($violenceAndGoreColumn . '_average')->default(0.0);
            $table->unsignedInteger($violenceAndGoreColumn . '_count')->default(0);
            $table->unsignedInteger($violenceAndGoreColumn . '_freq_brief')->default(0);
            $table->unsignedInteger($violenceAndGoreColumn . '_freq_occasional')->default(0);
            $table->unsignedInteger($violenceAndGoreColumn . '_freq_frequent')->default(0);
            $table->unsignedInteger($violenceAndGoreColumn . '_dep_implied')->default(0);
            $table->unsignedInteger($violenceAndGoreColumn . '_dep_shown')->default(0);
            $table->unsignedInteger($violenceAndGoreColumn . '_dep_graphic')->default(0);

            $profanityColumn = ParentalGuideCategory::Profanity()->columnName;
            $table->unsignedInteger($profanityColumn . '_rating_none')->default(0);
            $table->unsignedInteger($profanityColumn . '_rating_mild')->default(0);
            $table->unsignedInteger($profanityColumn . '_rating_moderate')->default(0);
            $table->unsignedInteger($profanityColumn . '_rating_severe')->default(0);
            $table->double($profanityColumn . '_average')->default(0.0);
            $table->unsignedInteger($profanityColumn . '_count')->default(0);
            $table->unsignedInteger($profanityColumn . '_freq_brief')->default(0);
            $table->unsignedInteger($profanityColumn . '_freq_occasional')->default(0);
            $table->unsignedInteger($profanityColumn . '_freq_frequent')->default(0);
            $table->unsignedInteger($profanityColumn . '_dep_implied')->default(0);
            $table->unsignedInteger($profanityColumn . '_dep_shown')->default(0);
            $table->unsignedInteger($profanityColumn . '_dep_graphic')->default(0);

            $alcoholDrugsSmokingColumn = ParentalGuideCategory::AlcoholDrugsAndSmoking()->columnName;
            $table->unsignedInteger($alcoholDrugsSmokingColumn . '_rating_none')->default(0);
            $table->unsignedInteger($alcoholDrugsSmokingColumn . '_rating_mild')->default(0);
            $table->unsignedInteger($alcoholDrugsSmokingColumn . '_rating_moderate')->default(0);
            $table->unsignedInteger($alcoholDrugsSmokingColumn . '_rating_severe')->default(0);
            $table->double($alcoholDrugsSmokingColumn . '_average')->default(0.0);
            $table->unsignedInteger($alcoholDrugsSmokingColumn . '_count')->default(0);
            $table->unsignedInteger($alcoholDrugsSmokingColumn . '_freq_brief')->default(0);
            $table->unsignedInteger($alcoholDrugsSmokingColumn . '_freq_occasional')->default(0);
            $table->unsignedInteger($alcoholDrugsSmokingColumn . '_freq_frequent')->default(0);
            $table->unsignedInteger($alcoholDrugsSmokingColumn . '_dep_implied')->default(0);
            $table->unsignedInteger($alcoholDrugsSmokingColumn . '_dep_shown')->default(0);
            $table->unsignedInteger($alcoholDrugsSmokingColumn . '_dep_graphic')->default(0);

            $frighteningIntenseColumn = ParentalGuideCategory::FrighteningAndIntenseScenes()->columnName;
            $table->unsignedInteger($frighteningIntenseColumn . '_rating_none')->default(0);
            $table->unsignedInteger($frighteningIntenseColumn . '_rating_mild')->default(0);
            $table->unsignedInteger($frighteningIntenseColumn . '_rating_moderate')->default(0);
            $table->unsignedInteger($frighteningIntenseColumn . '_rating_severe')->default(0);
            $table->double($frighteningIntenseColumn . '_average')->default(0.0);
            $table->unsignedInteger($frighteningIntenseColumn . '_count')->default(0);
            $table->unsignedInteger($frighteningIntenseColumn . '_freq_brief')->default(0);
            $table->unsignedInteger($frighteningIntenseColumn . '_freq_occasional')->default(0);
            $table->unsignedInteger($frighteningIntenseColumn . '_freq_frequent')->default(0);
            $table->unsignedInteger($frighteningIntenseColumn . '_dep_implied')->default(0);
            $table->unsignedInteger($frighteningIntenseColumn . '_dep_shown')->default(0);
            $table->unsignedInteger($frighteningIntenseColumn . '_dep_graphic')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table(ParentalGuideStat::TABLE_NAME, function (Blueprint $table) {
            // Set index key constraints
            $table->index('deleted_at');

            // Set unique key constraints
            $table->unique(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(ParentalGuideStat::TABLE_NAME);
    }
};
