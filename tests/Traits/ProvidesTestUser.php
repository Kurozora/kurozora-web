<?php

namespace Tests\Traits;

use App\Models\User;

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
    protected function initializeTestUser()
    {
        $this->user = User::factory()->create([
            'username'  => 'KurozoraTester',
            'email'     => 'tester@kurozora.app',
            'password'  => User::hashPass($this->userPassword),
            'biography' => 'Hi! This is my Kurozora account.'
        ]);
    }
}
