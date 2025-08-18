<?php

namespace App\Jobs;

use App\Enums\ParentalGuideCategory;
use App\Enums\ParentalGuideDepiction;
use App\Enums\ParentalGuideFrequency;
use App\Enums\ParentalGuideRating;
use App\Models\ParentalGuideEntry;
use App\Models\ParentalGuideStat;
use App\Services\ParentalGuideService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateParentalGuideStatsJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected string $modelType;
    protected string $modelID;

    /**
     * Create a new job instance.
     */
    public function __construct(string $modelType, int $modelID)
    {
        $this->modelType = $modelType;
        $this->modelID = $modelID;
    }

    /**
     * Execute the job.
     */
    public function handle(ParentalGuideService $parentalGuideService): void
    {
        $categories = ParentalGuideCategory::getInstances();
        $stats = ParentalGuideStat::firstOrNew([
            'model_type' => $this->modelType,
            'model_id' => $this->modelID,
        ]);
        $parentalGuideEntries = ParentalGuideEntry::with('user:id,reputation_count')
            ->where('model_type', '=', $this->modelType)
            ->where('model_id', '=', $this->modelID)
            ->whereIn('category', array_map(fn($category) => $category->value, $categories))
            ->get();

        foreach ($categories as $category) {
            $entries = $parentalGuideEntries->where('category.value', '=', $category->value);
            $calc = $parentalGuideService->weightedTrimmedMean($entries);

            $columnName = $category->columnName;

            // Average + total count
            $stats->setAttribute($columnName . '_average', $calc['average']);
            $stats->setAttribute($columnName . '_count', $calc['votes_count']);

            // frequency distribution
            $stats->setAttribute($columnName . '_freq_brief', $entries->where('frequency.value', '=', ParentalGuideFrequency::Brief)->count());
            $stats->setAttribute($columnName . '_freq_occasional', $entries->where('frequency.value', '=', ParentalGuideFrequency::Occasional)->count());
            $stats->setAttribute($columnName . '_freq_frequent', $entries->where('frequency.value', '=', ParentalGuideFrequency::Frequent)->count());

            // Depiction distribution
            $stats->setAttribute($columnName . '_dep_implied', $entries->where('depiction.value', '=', ParentalGuideDepiction::Implied)->count());
            $stats->setAttribute($columnName . '_dep_shown', $entries->where('depiction.value', '=', ParentalGuideDepiction::Shown)->count());
            $stats->setAttribute($columnName . '_dep_graphic', $entries->where('depiction.value', '=', ParentalGuideDepiction::Graphic)->count());

            // Rating distribution
            $stats->setAttribute($columnName . '_rating_none', $entries->where('rating.value', '=', ParentalGuideRating::None)->count());
            $stats->setAttribute($columnName . '_rating_mild', $entries->where('rating.value', '=', ParentalGuideRating::Mild)->count());
            $stats->setAttribute($columnName . '_rating_moderate', $entries->where('rating.value', '=', ParentalGuideRating::Moderate)->count());
            $stats->setAttribute($columnName . '_rating_severe', $entries->where('rating.value', '=', ParentalGuideRating::Severe)->count());
        }

        $stats->save();
    }
}
