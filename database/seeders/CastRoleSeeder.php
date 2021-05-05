<?php

namespace Database\Seeders;

use App\Models\CastRole;
use Illuminate\Database\Seeder;

class CastRoleSeeder extends Seeder
{
    /**
     * The available cast roles.
     *
     * @var array
     */
    protected array $castRoles = [
        [
            'name' => 'Protagonist',
            'description' => 'The leading character or one of the major characters.',
        ],
        [
            'name' => 'Deuteragonist',
            'description' => 'The secondary character such as the protagonist’s sidekick or love interest.',
        ],
        [
            'name' => 'Tritagonist',
            'description' => 'The third most important character that usually acts as the instigator or cause of the sufferings of the protagonist.',
        ],
        [
            'name' => 'Supporting Character',
            'description' => 'A character who is not the focus of the primary storyline.',
        ],
        [
            'name' => 'Antagonist',
            'description' => 'A character who is presented as the chief foe of the protagonist.',
        ],
        [
            'name' => 'Antihero',
            'description' => 'A main character who lacks conventional heroic qualities and attributes such as idealism, courage, and morality.',
        ],
        [
            'name' => 'Archenemy',
            'description' => 'The most prominent and worst enemy of the protagonist.',
        ],
        [
            'name' => 'Focal Character',
            'description' => 'The character on whom the audience is meant to place the majority of their interest and attention.',
        ],
        [
            'name' => 'Foil',
            'description' => 'A character who contrasts with the protagonist in order to better highlight or differentiate certain qualities of the protagonist.',
        ],
        [
            'name' => 'Narrator',
            'description' => 'A character, usually an unspecified literary voice, who delivers information about the story to the audience.',
        ],
        [
            'name' => 'Title Character',
            'description' => 'The character who is named or referred to in the title of the work.',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->castRoles as $castRole) {
            CastRole::create($castRole);
        }
    }
}
