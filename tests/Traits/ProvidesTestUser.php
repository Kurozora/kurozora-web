<?php

namespace Tests\Traits;

use App\Models\User;
use Hash;

trait ProvidesTestUser
{
    /** @var User $user */
    public User $user;

    /** @var string $userPassword */
    public string $userPassword = 'secret';

    /**
     * Creates the test user to be used in tests.
     *
     * @return void
     */
    protected function setupProvidesTestUser(): void
    {
        $this->user = User::factory()->create([
            'username' => 'KurozoraTester',
            'email' => 'tester@kurozora.app',
            'email_verified_at' => now(),
            'password' => Hash::make($this->userPassword),
            'biography' => 'Hi! This is my Kurozora account.',
        ]);
    }
}
