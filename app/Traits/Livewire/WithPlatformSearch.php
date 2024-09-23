<?php

namespace App\Traits\Livewire;

use App\Models\Platform;

trait WithPlatformSearch
{
    use WithSearch;

    /**
     * The model used for searching.
     *
     * @var string $searchModel
     */
    public static string $searchModel = Platform::class;

    /**
     * Redirect the user to a random platform.
     *
     * @return void
     */
    public function randomPlatform(): void
    {
        $platform = Platform::inRandomOrder()->first();
        $this->redirectRoute('platforms.details', $platform);
    }

    /**
     * Set the orderable attributes of the model.
     *
     * @return array
     */
    public function setOrderableAttributes(): array
    {
        return [
            'name' => [
                'title' => __('Name'),
                'options' => [
                    'Default' => null,
                    'A-Z' => 'asc',
                    'Z-A' => 'desc',
                ],
                'selected' => null,
            ],
            'type' => [
                'title' => __('Type'),
                'options' => [
                    'Default' => null,
                    'A-Z' => 'asc',
                    'Z-A' => 'desc',
                ],
                'selected' => null,
            ],
            'generation' => [
                'title' => __('Generation'),
                'options' => [
                    'Default' => null,
                    '1-9' => 'asc',
                    '9-1' => 'desc',
                ],
                'selected' => null,
            ],
            'started_at' => [
                'title' => __('Released On'),
                'options' => [
                    'Default' => null,
                    'Recent' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
            'ended_at' => [
                'title' => __('Discontinued On'),
                'options' => [
                    'Default' => null,
                    'Recent' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
        ];
    }

    /**
     * Set the filterable attributes of the model.
     *
     * @return array
     */
    public function setFilterableAttributes(): array
    {
        return Platform::webSearchFilters();
    }
}
