<?php

namespace App\Models;

use App\Enums\ParentalGuideCategory;
use App\Enums\ParentalGuideDepiction;
use App\Enums\ParentalGuideFrequency;
use App\Enums\ParentalGuideRating;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentalGuideStat extends KModel
{
    use SoftDeletes;

    // Table name
    const string TABLE_NAME = 'parental_guide_stats';
    protected $table = self::TABLE_NAME;

    /**
     * Returns the model of the parent guide stat.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The sentiment label.
     *
     * @param ParentalGuideCategory $category
     *
     * @return string
     */
    public function getCategorySentiment(ParentalGuideCategory $category): string
    {
        $frequency = $this->getAverageFrequency($category)?->description;
        $depiction = $this->getAverageDepiction($category)?->description;
        $rating = $this->getAverageRating($category)?->description;

        $categoryLabel = $category->description; // e.g. "Violence", "Nudity", "Profanity"

        return match ($category->value) {
            ParentalGuideCategory::SexAndNudity => trim("$frequency $depiction Nudity"),
            ParentalGuideCategory::ViolenceAndGore => trim("$frequency $depiction Violence"),
            ParentalGuideCategory::Profanity => trim("$frequency $rating Profanity"),
            ParentalGuideCategory::AlcoholDrugsAndSmoking => trim("$frequency $rating Alcohol/Drugs/Smoking"),
            ParentalGuideCategory::FrighteningAndIntenseScenes => trim("$frequency $depiction Intense Scenes"),
            default => trim("$frequency $rating $categoryLabel"),
        };
    }

    /**
     * Gets the average rating for a given category.
     *
     * @param ParentalGuideCategory $category
     *
     * @return ParentalGuideRating
     */
    public function getAverageRating(ParentalGuideCategory $category): ParentalGuideRating
    {
        $columnName = $category->columnName;
        $averageCategory = $this->getAttribute($columnName . '_average');
        return ParentalGuideRating::fromValue((int) round($averageCategory));
    }

    /**
     * Gets the average rating for a given category.
     *
     * @param ParentalGuideCategory $category
     *
     * @return int[]
     */
    public function getAverageRatingCount(ParentalGuideCategory $category): array
    {
        $columnName = $category->columnName;
        $averageRating = $this->getAverageRating($category);
        $ratingCounts = [
            ParentalGuideRating::None => $this->getAttribute($columnName . '_rating_none'),
            ParentalGuideRating::Mild => $this->getAttribute($columnName . '_rating_mild'),
            ParentalGuideRating::Moderate => $this->getAttribute($columnName . '_rating_moderate'),
            ParentalGuideRating::Severe => $this->getAttribute($columnName . '_rating_severe'),
        ];

        $totalVotes = array_sum($ratingCounts);

        if ($totalVotes === 0) {
            return [0, 0];
        }

        $matchingVotes = $ratingCounts[$averageRating->value] ?? 0;

        return [$matchingVotes, $totalVotes];
    }

    /**
     * Gets the average frequency for a given category.
     *
     * @param ParentalGuideCategory $category
     *
     * @return null|ParentalGuideFrequency
     */
    public function getAverageFrequency(ParentalGuideCategory $category): ?ParentalGuideFrequency
    {
        $columnName = $category->columnName;
        $frequencies = [
            ParentalGuideFrequency::Brief => $this->getAttribute($columnName . '_freq_brief'),
            ParentalGuideFrequency::Occasional => $this->getAttribute($columnName . '_freq_occasional'),
            ParentalGuideFrequency::Frequent => $this->getAttribute($columnName . '_freq_frequent'),
        ];
        $totalFrequency = array_sum($frequencies);

        if ($totalFrequency === 0) {
            return null;
        }

        // Weighted average
        $weightedSum = 0;

        foreach ($frequencies as $value => $count) {
            $weightedSum += $value * $count;
        }

        $averageFrequency = (int) round($weightedSum / $totalFrequency);

        return ParentalGuideFrequency::fromValue($averageFrequency);
    }

    /**
     * Gets the average depiction for a given category.
     *
     * @param ParentalGuideCategory $category
     *
     * @return null|ParentalGuideDepiction
     */
    public function getAverageDepiction(ParentalGuideCategory $category): ?ParentalGuideDepiction
    {
        $columnName = $category->columnName;
        $depictions = [
            ParentalGuideDepiction::Implied => $this->getAttribute($columnName . '_dep_implied'),
            ParentalGuideDepiction::Shown => $this->getAttribute($columnName . '_dep_shown'),
            ParentalGuideDepiction::Graphic => $this->getAttribute($columnName . '_dep_graphic'),
        ];
        $totalDepiction = array_sum($depictions);

        if ($totalDepiction === 0) {
            return null;
        }

        // Weighted average
        $weightedSum = 0;

        foreach ($depictions as $value => $count) {
            $weightedSum += $value * $count;
        }

        $averageDepiction = (int) round($weightedSum / $totalDepiction);

        return ParentalGuideDepiction::fromValue($averageDepiction);
    }
}
