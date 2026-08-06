<?php

namespace App\Http\Resources;

use App\Enums\ParentalGuideCategory;
use App\Enums\ParentalGuideRating;
use App\Models\ParentalGuideStat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentalGuideStatsResource extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var ParentalGuideStat|null $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        $categories = [];

        foreach (ParentalGuideCategory::getInstances() as $category) {
            $categories[] = $this->resolveCategory($category);
        }

        return [
            'categories' => $categories,
        ];
    }

    /**
     * Resolve the per-category aggregate map.
     *
     * @param ParentalGuideCategory $category
     *
     * @return array
     */
    private function resolveCategory(ParentalGuideCategory $category): array
    {
        if ($this->resource === null) {
            return $this->emptyAggregate();
        }

        $stat = $this->resource;
        $columnName = $category->columnName;
        $totalCount = (int) ($stat->getAttribute($columnName . '_count') ?? 0);

        if ($totalCount === 0) {
            return $this->emptyAggregate();
        }

        [$matchingCount] = $stat->getAverageRatingCount($category);
        $averageRating = $stat->getAverageRating($category);
        $averageFrequency = $stat->getAverageFrequency($category);
        $averageDepiction = $stat->getAverageDepiction($category);

        return [
            'averageRating' => (int) $averageRating->value,
            'averageFrequency' => $averageFrequency?->value,
            'averageDepiction' => $averageDepiction?->value,
            'ratingDistribution' => [
                'none' => (int) ($stat->getAttribute($columnName . '_rating_none') ?? 0),
                'mild' => (int) ($stat->getAttribute($columnName . '_rating_mild') ?? 0),
                'moderate' => (int) ($stat->getAttribute($columnName . '_rating_moderate') ?? 0),
                'severe' => (int) ($stat->getAttribute($columnName . '_rating_severe') ?? 0),
            ],
            'totalCount' => $totalCount,
            'matchingCount' => (int) $matchingCount,
        ];
    }

    /**
     * Empty per-category aggregate.
     *
     * @return array
     */
    private function emptyAggregate(): array
    {
        return [
            'averageRating' => ParentalGuideRating::None,
            'averageFrequency' => null,
            'averageDepiction' => null,
            'ratingDistribution' => [
                'none' => 0,
                'mild' => 0,
                'moderate' => 0,
                'severe' => 0,
            ],
            'totalCount' => 0,
            'matchingCount' => 0,
        ];
    }
}
