<?php

namespace Database\Seeders;

use App\Models\StaffRole;
use Illuminate\Database\Seeder;

class StaffRoleSeeder extends Seeder
{
    /**
     * The available staff roles.
     *
     * @var array $staffRoles
     */
    protected array $staffRoles = [
        [
            'name' => 'Other',
        ],
        [
            'name' => 'ADR Director',
        ],
        [
            'name' => 'Animation Character Design',
        ],
        [
            'name' => 'Animation Check',
        ],
        [
            'name' => 'Animation Director',
        ],
        [
            'name' => 'Animation Director Assistant',
        ],
        [
            'name' => 'Animation Producer',
        ],
        [
            'name' => 'Animation Production',
        ],
        [
            'name' => 'Animation Production Assistance',
        ],
        [
            'name' => 'Animation Supervision',
        ],
        [
            'name' => 'Animator',
        ],
        [
            'name' => 'Art',
        ],
        [
            'name' => 'Art Assistant',
        ],
        [
            'name' => 'Art Director',
        ],
        [
            'name' => 'Art Director Assistant',
        ],
        [
            'name' => 'Art Setting',
        ],
        [
            'name' => 'Assistance',
        ],
        [
            'name' => 'Assistant Animation Director',
        ],
        [
            'name' => 'Assistant Director',
        ],
        [
            'name' => 'Assistant Engineer',
        ],
        [
            'name' => 'Assistant Episode Director',
        ],
        [
            'name' => 'Assistant Producer',
        ],
        [
            'name' => 'Assistant Production Coordinator',
        ],
        [
            'name' => 'Associate Casting Director',
        ],
        [
            'name' => 'Associate Producer',
        ],
        [
            'name' => 'Audio Recording',
        ],
        [
            'name' => 'Audio Recording Adjustment',
        ],
        [
            'name' => 'Audio Recording Assistant',
        ],
        [
            'name' => 'Audio Recording Direction',
        ],
        [
            'name' => 'Audio Recording Production',
        ],
        [
            'name' => 'Audio Recording Studio',
        ],
        [
            'name' => 'Background Art',
        ],
        [
            'name' => 'Background Art Processing',
        ],
        [
            'name' => 'Casting Director',
        ],
        [
            'name' => 'Cell Inspection',
        ],
        [
            'name' => 'CGI Director',
        ],
        [
            'name' => 'CGI Producer',
        ],
        [
            'name' => 'CGI Production',
        ],
        [
            'name' => 'CGI Production Desk',
        ],
        [
            'name' => 'CGI Production Manager Assistant',
        ],
        [
            'name' => 'Character Design',
        ],
        [
            'name' => 'Chief Animation Director',
        ],
        [
            'name' => 'Chief Animation Supervisor',
        ],
        [
            'name' => 'Chief Director',
        ],
        [
            'name' => 'Chief Producer',
        ],
        [
            'name' => 'Chief Production Advancement',
        ],
        [
            'name' => 'Chief Supervision',
        ],
        [
            'name' => 'Co-Director',
        ],
        [
            'name' => 'Color Design',
        ],
        [
            'name' => 'Color Design Assistance',
        ],
        [
            'name' => 'Color Setting',
        ],
        [
            'name' => 'Color Specification Inspection',
        ],
        [
            'name' => 'Composer',
        ],
        [
            'name' => 'Concept Design',
        ],
        [
            'name' => 'Co-Producer',
        ],
        [
            'name' => 'Creative Producer',
        ],
        [
            'name' => 'Creator',
        ],
        [
            'name' => 'Creature Design',
        ],
        [
            'name' => 'Design',
        ],
        [
            'name' => 'Design Manager',
        ],
        [
            'name' => 'Design Production',
        ],
        [
            'name' => 'Dialogue Editing',
        ],
        [
            'name' => 'Digital Colouring',
        ],
        [
            'name' => 'Digital Director',
        ],
        [
            'name' => 'Digital Paint',
        ],
        [
            'name' => 'Digital Photography',
        ],
        [
            'name' => 'Digital Production',
        ],
        [
            'name' => 'Director',
        ],
        [
            'name' => 'Director Of Digital Photography',
        ],
        [
            'name' => 'Director Of Photography',
        ],
        [
            'name' => 'DVD Producer',
        ],
        [
            'name' => 'Editing',
        ],
        [
            'name' => 'Editor',
        ],
        [
            'name' => 'Editor Assistant',
        ],
        [
            'name' => 'Episode Director',
        ],
        [
            'name' => 'Executive Producer',
        ],
        [
            'name' => 'Film',
        ],
        [
            'name' => 'Film Editing',
        ],
        [
            'name' => 'Film Processing',
        ],
        [
            'name' => 'Financial Production',
        ],
        [
            'name' => 'General Manager',
        ],
        [
            'name' => 'HD Editor',
        ],
        [
            'name' => 'Image Board',
        ],
        [
            'name' => 'In-Between Animation',
        ],
        [
            'name' => 'In-Between Animation Assistance',
        ],
        [
            'name' => 'In-Between Animation Check',
        ],
        [
            'name' => 'In-Between Animation Inspection',
        ],
        [
            'name' => 'Inserted Song Performance',
        ],
        [
            'name' => 'Key Animation',
        ],
        [
            'name' => 'Layout',
        ],
        [
            'name' => 'Logo Design',
        ],
        [
            'name' => 'Lyrics',
        ],
        [
            'name' => 'Main Animator',
        ],
        [
            'name' => 'Main Title Design',
        ],
        [
            'name' => 'Main Title Photography',
        ],
        [
            'name' => 'Mechanical Design',
        ],
        [
            'name' => 'Music',
        ],
        [
            'name' => 'Music Arrangement',
        ],
        [
            'name' => 'Music Assistance',
        ],
        [
            'name' => 'Music Director',
        ],
        [
            'name' => 'Music Engineer',
        ],
        [
            'name' => 'Music Manager',
        ],
        [
            'name' => 'Music Producer',
        ],
        [
            'name' => 'Music Production',
        ],
        [
            'name' => 'Music Production Assistance',
        ],
        [
            'name' => 'Online Editing Supervision',
        ],
        [
            'name' => 'Online Editor',
        ],
        [
            'name' => 'Online Editor Manager',
        ],
        [
            'name' => 'Opening Animation',
        ],
        [
            'name' => 'Original Character Design',
        ],
        [
            'name' => 'Original Creator',
        ],
        [
            'name' => 'Original Illustration',
        ],
        [
            'name' => 'Original Story',
        ],
        [
            'name' => 'Photography',
        ],
        [
            'name' => 'Photography Assistance',
        ],
        [
            'name' => 'Planning',
        ],
        [
            'name' => 'Planning Cooperation',
        ],
        [
            'name' => 'Planning Manager',
        ],
        [
            'name' => 'Planning Producer',
        ],
        [
            'name' => 'Post-Production Assistant',
        ],
        [
            'name' => 'Principle Drawing',
        ],
        [
            'name' => 'Producer',
        ],
        [
            'name' => 'Production',
        ],
        [
            'name' => 'Production Assistant',
        ],
        [
            'name' => 'Production Control',
        ],
        [
            'name' => 'Production Coordination',
        ],
        [
            'name' => 'Production Desk',
        ],
        [
            'name' => 'Production Manager',
        ],
        [
            'name' => 'Production Office Work',
        ],
        [
            'name' => 'Production Studio',
        ],
        [
            'name' => 'Promotion',
        ],
        [
            'name' => 'Prop Design',
        ],
        [
            'name' => 'Publication',
        ],
        [
            'name' => 'Publicity',
        ],
        [
            'name' => 'Publicity Assistance',
        ],
        [
            'name' => 'Recording',
        ],
        [
            'name' => 'Recording Assistant',
        ],
        [
            'name' => 'Recording Engineer',
        ],
        [
            'name' => 'Re-Recording Mixing',
        ],
        [
            'name' => 'Screenplay',
        ],
        [
            'name' => 'Script',
        ],
        [
            'name' => 'Second Key Animator',
        ],
        [
            'name' => 'Selling Agency',
        ],
        [
            'name' => 'Series Composition',
        ],
        [
            'name' => 'Series Composition Assistant',
        ],
        [
            'name' => 'Series Director',
        ],
        [
            'name' => 'Series Episode Director',
        ],
        [
            'name' => 'Series Production Director',
        ],
        [
            'name' => 'Set Design',
        ],
        [
            'name' => 'Setting',
        ],
        [
            'name' => 'Setting Manager',
        ],
        [
            'name' => 'Setting Production',
        ],
        [
            'name' => 'Sound',
        ],
        [
            'name' => 'Sound Director',
        ],
        [
            'name' => 'Sound Effects',
        ],
        [
            'name' => 'Sound Manager',
        ],
        [
            'name' => 'Sound Mixer',
        ],
        [
            'name' => 'Sound Production',
        ],
        [
            'name' => 'Sound Supervisor',
        ],
        [
            'name' => 'Sound Work Manager',
        ],
        [
            'name' => 'Special Effects',
        ],
        [
            'name' => 'Sponsor',
        ],
        [
            'name' => 'Spotting',
        ],
        [
            'name' => 'Story',
        ],
        [
            'name' => 'Story & Art',
        ],
        [
            'name' => 'Story Composition',
        ],
        [
            'name' => 'Storyboard',
        ],
        [
            'name' => 'Supervision',
        ],
        [
            'name' => 'Theme Song Arrangement',
        ],
        [
            'name' => 'Theme Song Composition',
        ],
        [
            'name' => 'Theme Song Lyrics',
        ],
        [
            'name' => 'Theme Song Performance',
        ],
        [
            'name' => 'Titling',
        ],
        [
            'name' => 'Touch Up',
        ],
        [
            'name' => 'Touch Up Assistance',
        ],
        [
            'name' => 'Touch Up Inspection',
        ],
        [
            'name' => 'Touch Up Manager',
        ],
        [
            'name' => 'Two Dimensional Effects',
        ],
        [
            'name' => 'Two Dimensional Effects Chief',
        ],
        [
            'name' => 'Vocal',
        ],
        [
            'name' => 'Voice Actor',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->staffRoles as $staffRole) {
            StaffRole::create($staffRole);
        }
    }
}
