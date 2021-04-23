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
            'name'          => 'N',
            'description'   => 'Not Rated',
        ],
        [
            'name'          => 'G',
            'description'   => 'All Ages',
        ],
        [
            'name'          => 'PG'   ,
            'description'   => 'Children',
        ],
        [
            'name'          => 'PG-13',
            'description'   => 'Teens 13 or Older',
        ],
        [
            'name'          => 'R',
            'description'   => '17+ (violence & profanity)',
        ],
        [
            'name'          => 'R+',
            'description'   => 'Mild Nudity',
        ],
        [
            'name'          => 'Rx',
            'description'   => 'Hentai',
        ],
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
