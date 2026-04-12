<?php

namespace Database\Seeders;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaTypeCategory;
use App\Models\RatingCategory;
use Illuminate\Database\Seeder;

class RatingCategorySeeder extends Seeder
{
    /**
     * The list of rating categories.
     *
     * @var array $categories
     */
    protected array $categories = [
        // Universal categories (all media types)
        [
            'slug' => 'story',
            'name' => 'Story',
            'description' => 'The quality of the narrative, plot, and storytelling.',
            'weight' => 1.5,
        ],
        [
            'slug' => 'characters',
            'name' => 'Characters',
            'description' => 'The depth, development, and relatability of characters.',
            'weight' => 1.5,
        ],
        [
            'slug' => 'enjoyment',
            'name' => 'Enjoyment',
            'description' => 'Your personal enjoyment and entertainment value.',
            'weight' => 1.0,
        ],
        [
            'slug' => 'world_building',
            'name' => 'World Building',
            'description' => 'The depth and consistency of the fictional world.',
            'weight' => 1.0,
        ],
        [
            'slug' => 'pacing',
            'name' => 'Pacing',
            'description' => 'The flow and rhythm of the narrative.',
            'weight' => 1.0,
        ],
        [
            'slug' => 'emotional_impact',
            'name' => 'Emotional Impact',
            'description' => 'How effectively it evokes emotions.',
            'weight' => 1.0,
        ],
        // Anime-specific categories
        [
            'slug' => 'direction',
            'name' => 'Direction',
            'description' => 'The quality of directing and scene composition.',
            'weight' => 1.0,
        ],
        [
            'slug' => 'animation',
            'name' => 'Animation',
            'description' => 'The quality and fluidity of animation.',
            'weight' => 1.0,
        ],
        [
            'slug' => 'art',
            'name' => 'Art',
            'description' => 'The visual art style and aesthetic quality.',
            'weight' => 1.0,
        ],
        [
            'slug' => 'sound',
            'name' => 'Sound',
            'description' => 'Music, sound effects, and voice acting quality.',
            'weight' => 1.0,
        ],
        // Manga-specific categories
        [
            'slug' => 'paneling',
            'name' => 'Paneling',
            'description' => 'The layout and flow of panels.',
            'weight' => 1.0,
        ],
        // Game-specific categories
        [
            'slug' => 'gameplay',
            'name' => 'Gameplay',
            'description' => 'The mechanics and interactive experience.',
            'weight' => 1.5,
        ],
        [
            'slug' => 'graphics',
            'name' => 'Graphics',
            'description' => 'The visual quality and art style.',
            'weight' => 1.0,
        ],
        [
            'slug' => 'replayability',
            'name' => 'Replayability',
            'description' => 'The value of replaying the game.',
            'weight' => 0.75,
        ],
    ];

    /**
     * Media type to category mapping with display order.
     *
     * @var array $mediaTypeMapping
     */
    protected array $mediaTypeMapping = [
        Anime::class => [
            'story' => 1,
            'characters' => 2,
            'world_building' => 3,
            'pacing' => 4,
            'direction' => 5,
            'emotional_impact' => 6,
            'animation' => 7,
            'art' => 8,
            'sound' => 9,
            'enjoyment' => 10,
        ],
        Manga::class => [
            'story' => 1,
            'characters' => 2,
            'world_building' => 3,
            'pacing' => 4,
            'emotional_impact' => 5,
            'art' => 6,
            'paneling' => 7,
            'enjoyment' => 8,
        ],
        Game::class => [
            'story' => 1,
            'characters' => 2,
            'gameplay' => 3,
            'world_building' => 4,
            'graphics' => 5,
            'sound' => 6,
            'replayability' => 7,
            'enjoyment' => 8,
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Create rating categories
        $categoryModels = [];
        foreach ($this->categories as $category) {
            $categoryModels[$category['slug']] = RatingCategory::create($category);
        }

        // Create media type to category mappings
        foreach ($this->mediaTypeMapping as $mediaType => $categories) {
            foreach ($categories as $slug => $displayOrder) {
                if (isset($categoryModels[$slug])) {
                    MediaTypeCategory::create([
                        'model_type' => $mediaType,
                        'rating_category_id' => $categoryModels[$slug]->id,
                        'display_order' => $displayOrder,
                    ]);
                }
            }
        }
    }
}
