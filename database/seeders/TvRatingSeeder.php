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
        ],
        [
            'name'          => 'G',
            'description'   => 'All Ages',
        ],
        [
            'name'          => 'PG-12',
            'description'   => 'Parental Guidance Suggested',
        ],
        [
            'name'          => 'R15+',
            'description'   => 'Violence & Profanity',
        ],
        [
            'name'          => 'R18+',
            'description'   => 'Adults Only',
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->tvRatings as $tvRating) {
            TvRating::create([
                'name'          => $tvRating['name'],
                'description'   => $tvRating['description'],
            ]);
        }
    }
}
