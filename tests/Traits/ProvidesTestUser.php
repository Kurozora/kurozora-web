<?php

namespace Tests\API\Traits;

use App\User;

trait ProvidesTestUser {
    /** @var User $user */
    public $user;

    /**
     * Creates the test user to be used in tests.
     *
     * @return void
     */
    protected function initializeTestUser() {
        $this->user = factory(User::class)->create([
            'username' => 'KurozoraTester',
            'email' => 'tester@kurozora.app',
            'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
            'biography' => 'Hi! This is my Kurozora account.'
        ]);
    }
}
