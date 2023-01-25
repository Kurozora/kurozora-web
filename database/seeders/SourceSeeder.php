<?php

namespace Database\Seeders;

use App\Models\Source;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    /**
     * The available sources.
     *
     * @var array
     */
    protected array $sources = [
        [
            'name'          => 'Unknown',
            'description'   => 'Origin is unknown due to old age and other factors.',
        ],
        [
            'name'          => 'Original',
            'description'   => 'Series and film produced in-house.',
        ],
        [
            'name'          => 'Book',
            'description'   => 'Adapted from a written or printed work consisting of pages glued or sewn together.',
        ],
        [
            'name'          => 'Picture Book',
            'description'   => 'Adapted from a literary that combines visual and verbal narratives in a book format.',
        ],
        [
            'name'          => 'Manga',
            'description'   => 'Originally a manga that has been adapted.',
        ],
        [
            'name'          => 'Digital Manga',
            'description'   => 'Adapted from comic strips that are released as a digital book.',
        ],
        [
            'name'          => '4-Koma Manga',
            'description'   => 'Adapted from comic strips that generally consists of four panels per page.',
        ],
        [
            'name'          => 'Web Manga',
            'description'   => 'Adapted from comic strips that are released on websites.',
        ],
        [
            'name'          => 'Novel',
            'description'   => 'Adapted from a fictional story of book length',
        ],
        [
            'name'          => 'Light Novel',
            'description'   => 'Adapted from a book targeting teenagers and young adults.',
        ],
        [
            'name'          => 'Visual Novel',
            'description'   => 'Adapted from an interactive fiction video game which usually features a text-based story.',
        ],
        [
            'name'          => 'Game',
            'description'   => 'Adapted from a video game.',
        ],
        [
            'name'          => 'Card Game',
            'description'   => 'Adapted from a card-based game.',
        ],
        [
            'name'          => 'Music',
            'description'   => 'Adapted from a music video.',
        ],
        [
            'name'          => 'Radio',
            'description'   => 'Adapted from a radio and audio-only program.',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        foreach ($this->sources as $source) {
            Source::create($source);
        }
    }
}
