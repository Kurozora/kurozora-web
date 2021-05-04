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
            'description' => '',
        ],
        [
            'name' => 'Prequel',
            'description' => '',
        ],
        [
            'name' => 'Alternative Setting',
            'description' => '',
        ],
        [
            'name' => 'Alternative Version',
            'description' => '',
        ],
        [
            'name' => 'Side Story',
            'description' => '',
        ],
        [
            'name' => 'Summary',
            'description' => '',
        ],
        [
            'name' => 'Full Story',
            'description' => '',
        ],
        [
            'name' => 'Parent Story',
            'description' => '',
        ],
        [
            'name' => 'Spin-Off',
            'description' => '',
        ],
        [
            'name' => 'Adaptation',
            'description' => '',
        ],
        [
            'name' => 'Character',
            'description' => '',
        ],
        [
            'name' => 'Other',
            'description' => '',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->relations as $relation) {
            Relation::create($relation);
        }
    }
}
