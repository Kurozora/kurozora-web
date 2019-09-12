<?php

use App\Enums\UserRole;
use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Kurozora users
        User::create([
            'username'              => 'Usopp',
            'email'                 => 'mussesemou99@gmail.com',
            'password'              => '$2y$10$LFvuPaQpn6kccakk4sRABef223GV0.NJUJ94Xr.TAvswkCKJBisVK',
            'email_confirmation_id' => null,
            'role'                  => UserRole::Administrator
        ]);

        User::create([
            'username'              => 'Kirito',
            'email'                 => 'casillaskhoren1@gmail.com',
            'password'              => '$2y$10$LFvuPaQpn6kccakk4sRABef223GV0.NJUJ94Xr.TAvswkCKJBisVK',
            'email_confirmation_id' => null,
            'role'                  => UserRole::Administrator
        ]);

        /*
         * Apple test account
         *
         * password: KurozoraLovesApple4Ever!
         */
        User::create([
            'username'              => 'JohnAppleseed',
            'email'                 => 'john.appleseed@apple.com',
            'password'              => '$2y$10$/aVrkVAq4LT6FEEw3dNwguaM77MzoHB4.IpVoVxLLEKI4jyHuITii',
            'email_confirmation_id' => null
        ]);

        // 50 fake users
        factory(User::class, 50)->create();
    }
}
