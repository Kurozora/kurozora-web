<?php

namespace Database\Factories;

use App\Models\Anime;
use App\Models\AnimeRelations;
use App\Enums\AnimeRelationType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnimeRelationsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AnimeRelations::class;

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

        return [
            'anime_id'          => $anime,
            'related_anime_id'  => $relatedAnime,
            'type'              => AnimeRelationType::getRandomValue()
        ];
    }
}
