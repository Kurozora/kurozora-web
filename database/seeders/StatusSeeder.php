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
        ],
        [
            'type'          => 'anime',
            'name'          => 'Not Airing Yet',
            'description'   => 'To premiere on the announced date.',
        ],
        [
            'type'          => 'anime',
            'name'          => 'Currently Airing',
            'description'   => 'Airing is ongoing.',
        ],
        [
            'type'          => 'anime',
            'name'          => 'Finished Airing',
            'description'   => 'Airing has come to an end',
        ],
        [
            'type'          => 'anime',
            'name'          => 'On Hiatus',
            'description'   => 'Airing is on a break for a while.',
        ],
        [
            'type'          => 'manga',
            'name'          => 'To Be Announced',
            'description'   => 'No official release date has been announced.',
        ],
        [
            'type'          => 'manga',
            'name'          => 'Not Published Yet',
            'description'   => 'Will start publishing on the announced date.',
        ],
        [
            'type'          => 'manga',
            'name'          => 'Currently Publishing',
            'description'   => 'Publishing in ongoing.',
        ],
        [
            'type'          => 'manga',
            'name'          => 'Finished Publishing',
            'description'   => 'Publishing has come to an end.',
        ],
        [
            'type'          => 'manga',
            'name'          => 'On Hiatus',
            'description'   => 'Publishing is on a break for a while.',
        ],
        [
            'type'          => 'manga',
            'name'          => 'Discontinued',
            'description'   => 'Publishing has been stopped permanently.',
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
