<?php

namespace Database\Seeders;

use App\Models\MediaType;
use Illuminate\Database\Seeder;

class MediaTypeSeeder extends Seeder
{
    /**
     * The available media types.
     *
     * @var array
     */
    protected array $mediaTypes = [
        [
            'type'          => 'anime',
            'name'          => 'Unknown',
            'description'   => 'Type is unknown due to old age and other factors.',
        ],
        [
            'type'          => 'anime',
            'name'          => 'TV',
            'description'   => 'A show that aired on television.',
        ],
        [
            'type'          => 'anime',
            'name'          => 'OVA',
            'description'   => 'Original Video Animation in home video formats.',
        ],
        [
            'type'          => 'anime',
            'name'          => 'Movie',
            'description'   => 'A short or feature length film.',
        ],
        [
            'type'          => 'anime',
            'name'          => 'Special',
            'description'   => 'A standalone series with a self-contained story.',
        ],
        [
            'type'          => 'anime',
            'name'          => 'ONA',
            'description'   => 'Original Net Animation that is released on the internet.',
        ],
        [
            'type'          => 'anime',
            'name'          => 'Music',
            'description'   => 'A short film that integrates a song.',
        ],
        [
            'type'          => 'manga',
            'name'          => 'Unknown',
            'description'   => 'Type is unknown due to old age and other factors.',
        ],
        [
            'type'          => 'manga',
            'name'          => 'Doujinshi',
            'description'   => 'Self-published print works.',
        ],
        [
            'type'          => 'manga',
            'name'          => 'Manhwa',
            'description'   => 'Comic strips of Korean origin.',
        ],
        [
            'type'          => 'manga',
            'name'          => 'Manhua',
            'description'   => 'Comic strips of Chinese origin.',
        ],
        [
            'type'          => 'manga',
            'name'          => 'OEL',
            'description'   => 'An Original English-Language manga that is originally publish in English.',
        ],
        [
            'type'          => 'manga',
            'name'          => 'Novel',
            'description'   => 'A fictional story of book length.',
        ],
        [
            'type'          => 'manga',
            'name'          => 'Manga',
            'description'   => 'Comic strips of Japanese origin.',
        ],
        [
            'type'          => 'manga',
            'name'          => 'Light Novel',
            'description'   => 'A novel targeting teenagers and young adults.',
        ],
        [
            'type'          => 'manga',
            'name'          => 'One-shot',
            'description'   => 'A standalone issue with a self-contained story.',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->mediaTypes as $mediaType) {
            MediaType::create($mediaType);
        }
    }
}
