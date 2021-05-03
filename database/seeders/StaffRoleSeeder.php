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
            'name' => 'Animation Character Design',
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
            'name' => 'Assistant Director',
        ],
        [
            'name' => 'Assistant Episode Director',
        ],
        [
            'name' => 'Assistant Producer',
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
            'name' => 'Cell Inspection',
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
            'name' => 'Color Design',
        ],
        [
            'name' => 'Color Design Assistance',
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
            'name' => 'Creative Producer',
        ],
        [
            'name' => 'Creator',
        ],
        [
            'name' => 'Creature Design',
        ],
        [
            'name' => 'DVD Producer',
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
            'name' => 'Digital Colouring',
        ],
        [
            'name' => 'Digital Director',
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
            'name' => 'In Between Animation',
        ],
        [
            'name' => 'In Between Animation Assistance',
        ],
        [
            'name' => 'In Between Animation Check',
        ],
        [
            'name' => 'In Between Animation Inspection',
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
            'name' => 'Producer',
        ],
        [
            'name' => 'Production',
        ],
        [
            'name' => 'Production Assistance',
        ],
        [
            'name' => 'Production Assistant',
        ],
        [
            'name' => 'Production Control',
        ],
        [
            'name' => 'Production Desk',
        ],
        [
            'name' => 'Production Manager',
        ],
        [
            'name' => 'Production OfficeWork',
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
            'name' => 'Screenplay',
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
            'name' => 'Set Design',
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
            'name' => 'Sound Mixer',
        ],
        [
            'name' => 'Sound Production',
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
            'name' => 'Story Composition',
        ],
        [
            'name' => 'Storyboard',
        ],
        [
            'name' => 'Supervision',
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
