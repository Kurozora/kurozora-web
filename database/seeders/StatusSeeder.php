<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    protected array $status = [
        [
            'type'          => 'anime',
            'name'          => 'To Be Announced',
            'description'   => 'No official release date has been announced.',
            'color'         => '#3b82f6',
        ],
        [
            'type'          => 'anime',
            'name'          => 'Not Airing Yet',
            'description'   => 'To premiere on the announced date.',
            'color'         => '#f59f0b',
        ],
        [
            'type'          => 'anime',
            'name'          => 'Currently Airing',
            'description'   => 'Airing is ongoing.',
            'color'         => '#22c55e',
        ],
        [
            'type'          => 'anime',
            'name'          => 'Finished Airing',
            'description'   => 'Airing has come to an end',
            'color'         => '#ef4444',
        ],
        [
            'type'          => 'anime',
            'name'          => 'On Hiatus',
            'description'   => 'Airing is on a break for a while.',
            'color'         => '#71717a',
        ],
        [
            'type'          => 'manga',
            'name'          => 'To Be Announced',
            'description'   => 'No official release date has been announced.',
            'color'         => '#3b82f6',
        ],
        [
            'type'          => 'manga',
            'name'          => 'Not Published Yet',
            'description'   => 'Will start publishing on the announced date.',
            'color'         => '#f59f0b',
        ],
        [
            'type'          => 'manga',
            'name'          => 'Currently Publishing',
            'description'   => 'Publishing in ongoing.',
            'color'         => '#22c55e',
        ],
        [
            'type'          => 'manga',
            'name'          => 'Finished Publishing',
            'description'   => 'Publishing has come to an end.',
            'color'         => '#ef4444',
        ],
        [
            'type'          => 'manga',
            'name'          => 'On Hiatus',
            'description'   => 'Publishing is on a break for a while.',
            'color'         => '#71717a',
        ],
        [
            'type'          => 'manga',
            'name'          => 'Discontinued',
            'description'   => 'Publishing has been stopped permanently.',
            'color'         => '#000000',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->status as $status) {
            Status::create($status);
        }
    }
}
