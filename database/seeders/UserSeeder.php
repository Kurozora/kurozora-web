<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Create Kurozora admins
        /** @var User[] $admins */
        $admins = [];

        $admins[] = User::create([
            'username'          => 'Kirito',
            'email'             => 'casillaskhoren1@gmail.com',
            'password'          => '$2y$10$LFvuPaQpn6kccakk4sRABef223GV0.NJUJ94Xr.TAvswkCKJBisVK',
            'email_verified_at' => now(),
            'can_change_username'   => false,
            'tv_rating'             => -1,
        ]);

        $admins[] = User::create([
            'username'          => 'Usopp',
            'email'             => 'mussesemou99@gmail.com',
            'password'          => '$2y$10$LFvuPaQpn6kccakk4sRABef223GV0.NJUJ94Xr.TAvswkCKJBisVK',
            'email_verified_at' => now(),
            'can_change_username'   => false,
            'tv_rating'             => -1,
        ]);

        foreach($admins as $admin) {
            $admin->assignRole('admin');
        }

        /*
         * Apple test account
         *
         * password: KurozoraLovesApple4Ever!
         */
        User::create([
            'username'          => 'JohnAppleseed',
            'email'             => 'john.appleseed@apple.com',
            'password'          => '$2y$10$/aVrkVAq4LT6FEEw3dNwguaM77MzoHB4.IpVoVxLLEKI4jyHuITii',
            'email_verified_at' => now(),
            'can_change_username'   => false,
            'tv_rating'             => -1,
        ]);

        // 50 fake users
        User::factory(50)->create();
    }
}
