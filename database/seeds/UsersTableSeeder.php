<?php

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
        // Create Kurozora user
        User::create([
            'username'              => 'Kurozora',
            'email'                 => 'kurozoraapp@gmail.com',
            'password'              => '$2y$10$LFvuPaQpn6kccakk4sRABef223GV0.NJUJ94Xr.TAvswkCKJBisVK',
            'email_confirmation_id' => null,
            'role'                  => User::USER_ROLE_ADMINISTRATOR
        ]);
    }
}
