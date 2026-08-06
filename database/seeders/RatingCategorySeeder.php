<?php

namespace Database\Seeders;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\RatingCategory;
use Illuminate\Database\Seeder;

class RatingCategorySeeder extends Seeder
{
    /**
     * The list of rating categories per model type.
     *
     * @var array $categories
     */
    protected array $categories = [
        Anime::class => [
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
                'slug' => 'direction',
                'name' => 'Direction',
                'description' => 'The quality of directing and scene composition.',
                'weight' => 1.0,
            ],
            [
                'slug' => 'emotional_impact',
                'name' => 'Emotional Impact',
                'description' => 'How effectively it evokes emotions.',
                'weight' => 1.0,
            ],
            [
                'slug' => 'visuals',
                'name' => 'Visuals',
                'description' => 'The quality of the animation and art in general.',
                'weight' => 1.0,
            ],
            [
                'slug' => 'sound',
                'name' => 'Sound',
                'description' => 'Music, sound effects, and voice acting quality.',
                'weight' => 1.0,
            ],
            [
                'slug' => 'enjoyment',
                'name' => 'Enjoyment',
                'description' => 'Your personal enjoyment and entertainment value.',
                'weight' => 1.0,
            ],
        ],
        Manga::class => [
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
            [
                'slug' => 'visuals',
                'name' => 'Visuals',
                'description' => 'The quality of the art and paneling in general.',
                'weight' => 1.0,
            ],
            [
                'slug' => 'enjoyment',
                'name' => 'Enjoyment',
                'description' => 'Your personal enjoyment and entertainment value.',
                'weight' => 1.0,
            ],
        ],
        Game::class => [
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
                'slug' => 'gameplay',
                'name' => 'Gameplay',
                'description' => 'The mechanics and interactive experience.',
                'weight' => 1.5,
            ],
            [
                'slug' => 'world_building',
                'name' => 'World Building',
                'description' => 'The depth and consistency of the fictional world.',
                'weight' => 1.0,
            ],
            [
                'slug' => 'visuals',
                'name' => 'Visuals',
                'description' => 'The quality of the graphics and art in general.',
                'weight' => 1.0,
            ],
            [
                'slug' => 'sound',
                'name' => 'Sound',
                'description' => 'Music, sound effects, and voice acting quality.',
                'weight' => 1.0,
            ],
            [
                'slug' => 'replayability',
                'name' => 'Replayability',
                'description' => 'The value of replaying the game.',
                'weight' => 0.75,
            ],
            [
                'slug' => 'enjoyment',
                'name' => 'Enjoyment',
                'description' => 'Your personal enjoyment and entertainment value.',
                'weight' => 1.0,
            ],
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        foreach ($this->categories as $modelType => $categories) {
            foreach ($categories as $displayOrder => $category) {
                RatingCategory::updateOrCreate([
                    'model_type' => $modelType,
                    'slug' => $category['slug'],
                ], [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'weight' => $category['weight'],
                    'display_order' => $displayOrder + 1,
                ]);
            }
        }
    }
}
