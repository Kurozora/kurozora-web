<?php

namespace Database\Seeders;

use App\Models\Relation;
use Illuminate\Database\Seeder;

class RelationSeeder extends Seeder
{
    /**
     * The available relations.
     *
     * @var array $relations
     */
    protected array $relations = [
        [
            'name' => 'Sequel',
            'description' => 'Series that are a direct, chronological, continuation of the original story.',
        ],
        [
            'name' => 'Prequel',
            'description' => 'Series that tell a story which takes place before another one.',
        ],
        [
            'name' => 'Alternative Setting',
            'description' => 'Series that take place in the same universe, but with different characters.',
        ],
        [
            'name' => 'Alternative Version',
            'description' => 'Series that usually take place in the same universe with the same characters, but with a different storyline',
        ],
        [
            'name' => 'Side Story',
            'description' => 'Series that focus on a single perspective or point of view about a given situation, incident, or account within the same series.',
        ],
        [
            'name' => 'Summary',
            'description' => 'Series that sums up a season or storyline by showing clips of significant events.',
        ],
        [
            'name' => 'Full Story',
            'description' => 'The full story of a summarised series.',
        ],
        [
            'name' => 'Parent Story',
            'description' => 'The original series from which all other stories are derived.',
        ],
        [
            'name' => 'Spin-Off',
            'description' => 'Parts of successful series, for example the concept, are taken and given a second series of their own.',
        ],
        [
            'name' => 'Adaptation',
            'description' => 'Series adapted from a different source. Usually when a book or a game is adapted to a TV series, and vice versa.',
        ],
        [
            'name' => 'Character',
            'description' => 'Series that usually focus on, usually popular, characters of a show.',
        ],
        [
            'name' => 'Other',
            'description' => 'Series that don’t fall under one of the other relation types.',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        foreach ($this->relations as $relation) {
            Relation::create($relation);
        }
    }
}
