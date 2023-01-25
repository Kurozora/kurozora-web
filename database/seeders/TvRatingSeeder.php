<?php

namespace Database\Seeders;

use App\Models\TvRating;
use Illuminate\Database\Seeder;

class TvRatingSeeder extends Seeder
{
    /**
     * The list of ratings.
     *
     * @var array $tvRatings
     */
    protected array $tvRatings = [
        [
            'name'          => 'NR',
            'description'   => 'Not Rated',
            'weight'        => 1,
        ],
        [
            'name'          => 'G',
            'description'   => 'All Ages',
            'weight'        => 2,
        ],
        [
            'name'          => 'PG-12',
            'description'   => 'Parental Guidance Suggested',
            'weight'        => 3,
        ],
        [
            'name'          => 'R15+',
            'description'   => 'Violence & Profanity',
            'weight'        => 4,
        ],
        [
            'name'          => 'R18+',
            'description'   => 'Adults Only',
            'weight'        => 5,
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        foreach ($this->tvRatings as $tvRating) {
            TvRating::create($tvRating);
        }
    }
}
