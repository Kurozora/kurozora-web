<?php

namespace Database\Factories;

use App\Models\Anime;
use App\Models\MediaRelation;
use App\Models\Relation;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaRelationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MediaRelation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $anime = Anime::inRandomOrder()->first();
        if ($anime == null) {
            $anime = Anime::factory()->create();
        }

        $relatedAnime = Anime::whereNotIn('id', [$anime->id])->inRandomOrder()->first();
        if ($relatedAnime == null) {
            $relatedAnime = Anime::factory()->create();
        }

        $relation = Relation::inRandomOrder()->first();
        if ($relation == null) {
            $relation = Relation::factory()->create();
        }

        return [
            'media_id'      => $anime,
            'media_type'    => $this->faker->randomElement(['anime', 'manga']),
            'relation_id'   => $relation,
            'related_id'    => $relatedAnime,
            'related_type'  => $this->faker->randomElement(['anime', 'manga']),
        ];
    }
}
