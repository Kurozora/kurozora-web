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
            'name'          => 'Other',
            'description'   => '',
        ],
        [
            'name'          => 'ADR Director',
            'description'   => '',
        ],
        [
            'name'          => 'Animation Character Design',
            'description'   => '',
        ],
        [
            'name'          => 'Animation Check',
            'description'   => '',
        ],
        [
            'name'          => 'Animation Director',
            'description'   => '',
        ],
        [
            'name'          => 'Animation Director Assistant',
            'description'   => '',
        ],
        [
            'name'          => 'Animation Producer',
            'description'   => '',
        ],
        [
            'name'          => 'Animation Production',
            'description'   => '',
        ],
        [
            'name'          => 'Animation Production Assistance',
            'description'   => '',
        ],
        [
            'name'          => 'Animation Supervision',
            'description'   => '',
        ],
        [
            'name'          => 'Animator',
            'description'   => '',
        ],
        [
            'name'          => 'Art',
            'description'   => '',
        ],
        [
            'name'          => 'Art Assistant',
            'description'   => '',
        ],
        [
            'name'          => 'Art Director',
            'description'   => '',
        ],
        [
            'name'          => 'Art Director Assistant',
            'description'   => '',
        ],
        [
            'name'          => 'Art Setting',
            'description'   => '',
        ],
        [
            'name'          => 'Assistance',
            'description'   => '',
        ],
        [
            'name'          => 'Assistant Animation Director',
            'description'   => '',
        ],
        [
            'name'          => 'Assistant Director',
            'description'   => '',
        ],
        [
            'name'          => 'Assistant Engineer',
            'description'   => '',
        ],
        [
            'name'          => 'Assistant Episode Director',
            'description'   => '',
        ],
        [
            'name'          => 'Assistant Producer',
            'description'   => '',
        ],
        [
            'name'          => 'Assistant Production Coordinator',
            'description'   => '',
        ],
        [
            'name'          => 'Associate Casting Director',
            'description'   => '',
        ],
        [
            'name'          => 'Associate Producer',
            'description'   => '',
        ],
        [
            'name'          => 'Audio Recording',
            'description'   => '',
        ],
        [
            'name'          => 'Audio Recording Adjustment',
            'description'   => '',
        ],
        [
            'name'          => 'Audio Recording Assistant',
            'description'   => '',
        ],
        [
            'name'          => 'Audio Recording Direction',
            'description'   => '',
        ],
        [
            'name'          => 'Audio Recording Production',
            'description'   => '',
        ],
        [
            'name'          => 'Audio Recording Studio',
            'description'   => '',
        ],
        [
            'name'          => 'Background Art',
            'description'   => '',
        ],
        [
            'name'          => 'Background Art Processing',
            'description'   => '',
        ],
        [
            'name'          => 'Casting Director',
            'description'   => '',
        ],
        [
            'name'          => 'Cell Inspection',
            'description'   => '',
        ],
        [
            'name'          => 'CGI Director',
            'description'   => '',
        ],
        [
            'name'          => 'CGI Producer',
            'description'   => '',
        ],
        [
            'name'          => 'CGI Production',
            'description'   => '',
        ],
        [
            'name'          => 'CGI Production Desk',
            'description'   => '',
        ],
        [
            'name'          => 'CGI Production Manager Assistant',
            'description'   => '',
        ],
        [
            'name'          => 'Character Design',
            'description'   => '',
        ],
        [
            'name'          => 'Chief Animation Director',
            'description'   => '',
        ],
        [
            'name'          => 'Chief Animation Supervisor',
            'description'   => '',
        ],
        [
            'name'          => 'Chief Director',
            'description'   => '',
        ],
        [
            'name'          => 'Chief Producer',
            'description'   => '',
        ],
        [
            'name'          => 'Chief Production Advancement',
            'description'   => '',
        ],
        [
            'name'          => 'Chief Supervision',
            'description'   => '',
        ],
        [
            'name'          => 'Co-Director',
            'description'   => '',
        ],
        [
            'name'          => 'Color Design',
            'description'   => '',
        ],
        [
            'name'          => 'Color Design Assistance',
            'description'   => '',
        ],
        [
            'name'          => 'Color Setting',
            'description'   => '',
        ],
        [
            'name'          => 'Color Specification Inspection',
            'description'   => '',
        ],
        [
            'name'          => 'Composer',
            'description'   => '',
        ],
        [
            'name'          => 'Concept Design',
            'description'   => '',
        ],
        [
            'name'          => 'Co-Producer',
            'description'   => '',
        ],
        [
            'name'          => 'Creative Producer',
            'description'   => '',
        ],
        [
            'name'          => 'Creator',
            'description'   => '',
        ],
        [
            'name'          => 'Creature Design',
            'description'   => '',
        ],
        [
            'name'          => 'Design',
            'description'   => '',
        ],
        [
            'name'          => 'Design Manager',
            'description'   => '',
        ],
        [
            'name'          => 'Design Production',
            'description'   => '',
        ],
        [
            'name'          => 'Dialogue Editing',
            'description'   => '',
        ],
        [
            'name'          => 'Digital Colouring',
            'description'   => '',
        ],
        [
            'name'          => 'Digital Director',
            'description'   => '',
        ],
        [
            'name'          => 'Digital Paint',
            'description'   => '',
        ],
        [
            'name'          => 'Digital Photography',
            'description'   => '',
        ],
        [
            'name'          => 'Digital Production',
            'description'   => '',
        ],
        [
            'name'          => 'Director',
            'description'   => '',
        ],
        [
            'name'          => 'Director Of Digital Photography',
            'description'   => '',
        ],
        [
            'name'          => 'Director Of Photography',
            'description'   => '',
        ],
        [
            'name'          => 'DVD Producer',
            'description'   => '',
        ],
        [
            'name'          => 'Editing',
            'description'   => '',
        ],
        [
            'name'          => 'Editor',
            'description'   => '',
        ],
        [
            'name'          => 'Editor Assistant',
            'description'   => '',
        ],
        [
            'name'          => 'Episode Director',
            'description'   => '',
        ],
        [
            'name'          => 'Executive Producer',
            'description'   => '',
        ],
        [
            'name'          => 'Film',
            'description'   => '',
        ],
        [
            'name'          => 'Film Editing',
            'description'   => '',
        ],
        [
            'name'          => 'Film Processing',
            'description'   => '',
        ],
        [
            'name'          => 'Financial Production',
            'description'   => '',
        ],
        [
            'name'          => 'General Manager',
            'description'   => '',
        ],
        [
            'name'          => 'HD Editor',
            'description'   => '',
        ],
        [
            'name'          => 'Image Board',
            'description'   => '',
        ],
        [
            'name'          => 'In-Between Animation',
            'description'   => '',
        ],
        [
            'name'          => 'In-Between Animation Assistance',
            'description'   => '',
        ],
        [
            'name'          => 'In-Between Animation Check',
            'description'   => '',
        ],
        [
            'name'          => 'In-Between Animation Inspection',
            'description'   => '',
        ],
        [
            'name'          => 'Inserted Song Performance',
            'description'   => '',
        ],
        [
            'name'          => 'Key Animation',
            'description'   => '',
        ],
        [
            'name'          => 'Layout',
            'description'   => '',
        ],
        [
            'name'          => 'Logo Design',
            'description'   => '',
        ],
        [
            'name'          => 'Lyrics',
            'description'   => '',
        ],
        [
            'name'          => 'Main Animator',
            'description'   => '',
        ],
        [
            'name'          => 'Main Title Design',
            'description'   => '',
        ],
        [
            'name'          => 'Main Title Photography',
            'description'   => '',
        ],
        [
            'name'          => 'Mechanical Design',
            'description'   => '',
        ],
        [
            'name'          => 'Music',
            'description'   => '',
        ],
        [
            'name'          => 'Music Arrangement',
            'description'   => '',
        ],
        [
            'name'          => 'Music Assistance',
            'description'   => '',
        ],
        [
            'name'          => 'Music Director',
            'description'   => '',
        ],
        [
            'name'          => 'Music Engineer',
            'description'   => '',
        ],
        [
            'name'          => 'Music Manager',
            'description'   => '',
        ],
        [
            'name'          => 'Music Producer',
            'description'   => '',
        ],
        [
            'name'          => 'Music Production',
            'description'   => '',
        ],
        [
            'name'          => 'Music Production Assistance',
            'description'   => '',
        ],
        [
            'name'          => 'Online Editing Supervision',
            'description'   => '',
        ],
        [
            'name'          => 'Online Editor',
            'description'   => '',
        ],
        [
            'name'          => 'Online Editor Manager',
            'description'   => '',
        ],
        [
            'name'          => 'Opening Animation',
            'description'   => '',
        ],
        [
            'name'          => 'Original Character Design',
            'description'   => '',
        ],
        [
            'name'          => 'Original Creator',
            'description'   => '',
        ],
        [
            'name'          => 'Original Illustration',
            'description'   => '',
        ],
        [
            'name'          => 'Original Story',
            'description'   => '',
        ],
        [
            'name'          => 'Photography',
            'description'   => '',
        ],
        [
            'name'          => 'Photography Assistance',
            'description'   => '',
        ],
        [
            'name'          => 'Planning',
            'description'   => '',
        ],
        [
            'name'          => 'Planning Cooperation',
            'description'   => '',
        ],
        [
            'name'          => 'Planning Manager',
            'description'   => '',
        ],
        [
            'name'          => 'Planning Producer',
            'description'   => '',
        ],
        [
            'name'          => 'Post-Production Assistant',
            'description'   => '',
        ],
        [
            'name'          => 'Principle Drawing',
            'description'   => '',
        ],
        [
            'name'          => 'Producer',
            'description'   => '',
        ],
        [
            'name'          => 'Production',
            'description'   => '',
        ],
        [
            'name'          => 'Production Assistant',
            'description'   => '',
        ],
        [
            'name'          => 'Production Control',
            'description'   => '',
        ],
        [
            'name'          => 'Production Coordination',
            'description'   => '',
        ],
        [
            'name'          => 'Production Desk',
            'description'   => '',
        ],
        [
            'name'          => 'Production Manager',
            'description'   => '',
        ],
        [
            'name'          => 'Production Office Work',
            'description'   => '',
        ],
        [
            'name'          => 'Production Studio',
            'description'   => '',
        ],
        [
            'name'          => 'Promotion',
            'description'   => '',
        ],
        [
            'name'          => 'Prop Design',
            'description'   => '',
        ],
        [
            'name'          => 'Publication',
            'description'   => '',
        ],
        [
            'name'          => 'Publicity',
            'description'   => '',
        ],
        [
            'name'          => 'Publicity Assistance',
            'description'   => '',
        ],
        [
            'name'          => 'Recording',
            'description'   => '',
        ],
        [
            'name'          => 'Recording Assistant',
            'description'   => '',
        ],
        [
            'name'          => 'Recording Engineer',
            'description'   => '',
        ],
        [
            'name'          => 'Re-Recording Mixing',
            'description'   => '',
        ],
        [
            'name'          => 'Screenplay',
            'description'   => '',
        ],
        [
            'name'          => 'Script',
            'description'   => '',
        ],
        [
            'name'          => 'Second Key Animator',
            'description'   => '',
        ],
        [
            'name'          => 'Selling Agency',
            'description'   => '',
        ],
        [
            'name'          => 'Series Composition',
            'description'   => '',
        ],
        [
            'name'          => 'Series Composition Assistant',
            'description'   => '',
        ],
        [
            'name'          => 'Series Director',
            'description'   => '',
        ],
        [
            'name'          => 'Series Episode Director',
            'description'   => '',
        ],
        [
            'name'          => 'Series Production Director',
            'description'   => '',
        ],
        [
            'name'          => 'Set Design',
            'description'   => '',
        ],
        [
            'name'          => 'Setting',
            'description'   => '',
        ],
        [
            'name'          => 'Setting Manager',
            'description'   => '',
        ],
        [
            'name'          => 'Setting Production',
            'description'   => '',
        ],
        [
            'name'          => 'Sound',
            'description'   => '',
        ],
        [
            'name'          => 'Sound Director',
            'description'   => '',
        ],
        [
            'name'          => 'Sound Effects',
            'description'   => '',
        ],
        [
            'name'          => 'Sound Manager',
            'description'   => '',
        ],
        [
            'name'          => 'Sound Mixer',
            'description'   => '',
        ],
        [
            'name'          => 'Sound Production',
            'description'   => '',
        ],
        [
            'name'          => 'Sound Supervisor',
            'description'   => '',
        ],
        [
            'name'          => 'Sound Work Manager',
            'description'   => '',
        ],
        [
            'name'          => 'Special Effects',
            'description'   => '',
        ],
        [
            'name'          => 'Sponsor',
            'description'   => '',
        ],
        [
            'name'          => 'Spotting',
            'description'   => '',
        ],
        [
            'name'          => 'Story',
            'description'   => '',
        ],
        [
            'name'          => 'Story & Art',
            'description'   => '',
        ],
        [
            'name'          => 'Story Composition',
            'description'   => '',
        ],
        [
            'name'          => 'Storyboard',
            'description'   => '',
        ],
        [
            'name'          => 'Supervision',
            'description'   => '',
        ],
        [
            'name'          => 'Theme Song Arrangement',
            'description'   => '',
        ],
        [
            'name'          => 'Theme Song Composition',
            'description'   => '',
        ],
        [
            'name'          => 'Theme Song Lyrics',
            'description'   => '',
        ],
        [
            'name'          => 'Theme Song Performance',
            'description'   => '',
        ],
        [
            'name'          => 'Titling',
            'description'   => '',
        ],
        [
            'name'          => 'Touch Up',
            'description'   => '',
        ],
        [
            'name'          => 'Touch Up Assistance',
            'description'   => '',
        ],
        [
            'name'          => 'Touch Up Inspection',
            'description'   => '',
        ],
        [
            'name'          => 'Touch Up Manager',
            'description'   => '',
        ],
        [
            'name'          => 'Two Dimensional Effects',
            'description'   => '',
        ],
        [
            'name'          => 'Two Dimensional Effects Chief',
            'description'   => '',
        ],
        [
            'name'          => 'Vocal',
            'description'   => '',
        ],
        [
            'name'          => 'Voice Actor',
            'description'   => '',
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
