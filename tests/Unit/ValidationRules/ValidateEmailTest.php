<?php

namespace Tests\Unit\ValidationRules;

use App\Models\User;
use App\Rules\ValidateEmail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidateEmailTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A collection of email addresses that are valid.
     *
     * @var Collection $validEmailAddresses
     */
    private Collection $validEmailAddresses;

    /**
     * A collection of email addresses that are invalid.
     *
     * @var Collection $invalidEmailAddresses
     */
    private Collection $invalidEmailAddresses;

    /**
     * The email validation rule.
     *
     * @var ValidateEmail $rule
     */
    private ValidateEmail $rule;

    public function setUp(): void
    {
        parent::setUp();

        $this->rule = new ValidateEmail();

        // Source: https://gist.github.com/cjaoude/fd9910626629b53c4d25
        $this->validEmailAddresses = collect([
            'info@kurozora.app',
            'john490+1@gmail.com',
            'my-name-is-mark@hotmail.com',
            '"email"@kurozora.app',
            'email@subdomain.kurozora.app',
            'firstname.lastname@kurozora.app'
        ]);

        $this->invalidEmailAddresses = collect([
            '',
            'plainaddress',
            '#@%^%#$@#$@#.com',
            '@example.com',
            'Joe Smith <email@example.com>',
            'email.example.com',
            'email@-example.com',
            'email@111.222.333.44444',
            'Abc..123@example.com',
            '.email@example.com'
        ]);
    }

    /**
     * Valid email addresses pass.
     *
     * @return void
     */
    #[Test]
    public function valid_email_addresses_pass(): void
    {
        foreach($this->validEmailAddresses as $email) {
            $message = '';

            $this->rule->validate('email', $email, function ($error) use (&$message) {
                $message = $error;
            });

            $this->assertEmpty($message, $email . ' did not pass.' . $message);
        }
    }

    /**
     * Invalid email addresses dont pass.
     *
     * @return void
     */
    #[Test]
    public function invalid_email_addresses_dont_pass(): void
    {
        foreach($this->invalidEmailAddresses as $email) {
            $message = '';

            $this->rule->validate('email', $email, function ($error) use (&$message) {
                $message = $error;
            });

            $this->assertNotEmpty($message, $email . ' did not pass.' . $message);
        }
    }

    /**
     * Must be taken option works.
     *
     * @return void
     */
    #[Test]
    public function must_be_taken_option_works(): void
    {
        $this->rule = new ValidateEmail(['must-be-taken' => true]);

        // Take a random valid email
        $email = $this->validEmailAddresses->random();

        // The email should not pass, because it is not taken
        $message = '';

        $this->rule->validate('email', $email, function ($error) use (&$message) {
            $message = $error;
        });

        $this->assertNotEmpty($message, $email . ' did not pass.' . $message);

        // Create a user account with the email
        User::factory()->create(['email' => $email]);

        // The email should pass, because it is taken
        $message = '';

        $this->rule->validate('email', $email, function ($error) use (&$message) {
            $message = $error;
        });

        $this->assertEmpty($message, $email . ' did not pass.' . $message);
    }

    /**
     * Must be available option works.
     *
     * @return void
     */
    #[Test]
    public function must_be_available_option_works(): void
    {
        $this->rule = new ValidateEmail(['must-be-available' => true]);

        // Take a random valid email
        $email = $this->validEmailAddresses->random();

        // The email should pass, because it is available
        $message = '';

        $this->rule->validate('email', $email, function ($error) use (&$message) {
            $message = $error;
        });

        $this->assertEmpty($message, $email . ' did not pass.' . $message);

        // Create a user account with the email
        User::factory()->create(['email' => $email]);

        // The email should not pass, because it is taken
        $message = '';

        $this->rule->validate('email', $email, function ($error) use (&$message) {
            $message = $error;
        });

        $this->assertNotEmpty($message, $email . ' did not pass.' . $message);
    }

    /**
     * Must be available and must be taken options cannot be used at the same time.
     *
     * @return void
     */
    #[Test]
    public function must_be_available_and_must_be_taken_options_cannot_be_used_at_the_same_time(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ValidateEmail([
            'must-be-available' => true,
            'must-be-taken'     => true
        ]);
    }
}
