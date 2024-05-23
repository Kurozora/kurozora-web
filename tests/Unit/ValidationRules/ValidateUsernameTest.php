<?php

namespace Tests\Unit\ValidationRules;

use App\Models\User;
use App\Rules\ValidateUsername;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidateUsernameTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A collection of username addresses that are valid.
     *
     * @var Collection $validUsername
     */
    private Collection $validUsername;

    /**
     * A collection of username addresses that are invalid.
     *
     * @var Collection $invalidUsername
     */
    private Collection $invalidUsername;

    /**
     * The username validation rule.
     *
     * @var ValidateUsername $rule
     */
    private ValidateUsername $rule;

    public function setUp(): void
    {
        parent::setUp();

        $this->rule = new ValidateUsername();

        // Source: https://gist.github.com/cjaoude/fd9910626629b53c4d25
        $this->validUsername = collect([
            '1',
            'abc',
            'Siesta2349',
            'campfire',
            'Iron_Pug',
            'Golden Goose',
            'electric-mouse',
        ]);

        $this->invalidUsername = collect([
            '',
            '@example.com',
            'this is a very long username that exceeds the max char limit or at least it should',
        ]);
    }

    /**
     * Valid username pass.
     *
     * @return void
     */
    #[Test]
    public function valid_username_pass()
    {
        foreach ($this->validUsername as $username) {
            $message = '';

            $this->rule->validate('username', $username, function ($error) use (&$message) {
                $message = $error;
            });

            $this->assertEmpty($message, $username . ' did not pass. ' . $message);
        }
    }

    /**
     * Invalid username dont pass.
     *
     * @return void
     */
    #[Test]
    public function invalid_username_dont_pass()
    {
        foreach ($this->invalidUsername as $username) {
            $message = '';

            $this->rule->validate('username', $username, function ($error) use (&$message) {
                $message = $error;
            });

            $this->assertNotEmpty($message, $username . ' did not pass. ' . $message);
        }
    }

    /**
     * Username is taken.
     *
     * @return void
     */
    #[Test]
    public function username_is_taken()
    {
        $this->rule = new ValidateUsername();

        // Take a random valid username
        $username = $this->validUsername->random();

        // The username should pass, because it is available
        $message = '';

        $this->rule->validate('username', $username, function ($error) use (&$message) {
            $message = $error;
        });

        $this->assertEmpty($message, $username . ' did not pass. ' . $message);

        // Create a user account with the username
        User::factory()->create(['username' => $username]);

        // The username should not pass, because it is taken
        $message = '';

        $this->rule->validate('username', $username, function ($error) use (&$message) {
            $message = $error;
        });

        $this->assertNotEmpty($message, $username . ' did not pass. ' . $message);
    }
}
