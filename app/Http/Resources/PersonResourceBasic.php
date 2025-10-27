<?php

namespace App\Http\Resources;

use App\Enums\MediaCollection;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResourceBasic extends JsonResource
{
    /**
     * The resource instance.
     *
     * @var Person $resource
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $resource = PersonResourceIdentity::make($this->resource)->toArray($request);
        $resource = array_merge($resource, [
            'attributes' => [
                'slug' => $this->resource->slug,
                'profile' => MediaResource::make($this->resource->getFirstMedia(MediaCollection::Profile)),
                'fullName' => $this->resource->full_name,
                'fullGivenName' => $this->resource->full_given_name,
                'alternativeNames' => $this->resource->alternative_names,
                'age' => $this->resource->age_string,
                'birthdate' => $this->resource->birthdate?->timestamp,
                'deceasedDate' => $this->resource->deceased_date?->timestamp,
                'about' => $this->resource->about,
                'shortDescription' => $this->resource->short_description,
                'websiteURLs' => $this->resource->website_urls,
                'astrologicalSign' => $this->resource->astrological_sign?->description,
                'stats' => MediaStatsResource::make($this->resource->mediaStat),
            ]
        ]);

        if (auth()->check()) {
            $resource['attributes'] = array_merge($resource['attributes'], $this->getUserSpecificDetails());
        }

        return $resource;
    }

    /**
     * Returns the user-specific details for the resource.
     *
     * @return array
     */
    protected function getUserSpecificDetails(): array
    {
        $givenRating = $this->resource->mediaRatings->first();

        // Return the array
        return [
            'givenRating' => (double) $givenRating?->rating,
            'givenReview' => $givenRating?->description,
        ];
    }
}
