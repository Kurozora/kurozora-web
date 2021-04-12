<?php

namespace Tests\Unit\ValidationRules;

use App\Rules\ValidateEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Collection;
use InvalidArgumentException;
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

    /** @var ValidateEmail $rule */
    private ValidateEmail $rule;

    public function setUp(): void
    {
        parent::setUp();

        $this->rule = new ValidateEmail();

        // Source: https://gist.github.com/cjaoude/fd9910626629b53c4d25
        $this->validEmailAddresses = collect([
            'john@example.com',
            'info@kurozora.app',
            'john490+1@gmail.com',
            'my-name-is-mark@hotmail.com',
            'email@example.museum',
            '"email"@example.com',
            'email@subdomain.example.com',
            'firstname.lastname@example.com'
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

    /** @test */
    function valid_email_addresses_pass()
    {
        foreach($this->validEmailAddresses as $email) {
            $this->assertTrue($this->rule->passes('email', $email), "$email did not pass, while it should have!");
        }
    }

    /** @test */
    function invalid_email_addresses_dont_pass()
    {
        foreach($this->invalidEmailAddresses as $email) {
            $this->assertFalse($this->rule->passes('email', $email), "$email passed, while it should not have!");
        }
    }

    /** @test */
    function must_be_taken_option_works()
    {
        $this->rule = new ValidateEmail(['must-be-taken' => true]);

        // Take a random valid email
        $email = $this->validEmailAddresses->random();

        // The email should not pass, because it is not taken
        $this->assertFalse($this->rule->passes('email', $email), "$email passed, while it should not have!");

        // Create a user account with the email
        User::factory()->create(['email' => $email]);

        // The email should pass, because it is taken
        $this->assertTrue($this->rule->passes('email', $email), "$email did not pass, while it should have!");
    }

    /** @test */
    function must_be_available_option_works()
    {
        $this->rule = new ValidateEmail(['must-be-available' => true]);

        // Take a random valid email
        $email = $this->validEmailAddresses->random();

        // The email should pass, because it is available
        $this->assertTrue($this->rule->passes('email', $email), "$email did not pass, while it should have!");

        // Create a user account with the email
        User::factory()->create(['email' => $email]);

        // The email should not pass, because it is taken
        $this->assertFalse($this->rule->passes('email', $email), "$email passed, while it should not have!");
    }

    /** @test */
    function must_be_available_and_must_be_taken_options_cannot_be_used_at_the_same_time()
    {
        $this->expectException(InvalidArgumentException::class);

        new ValidateEmail([
            'must-be-available' => true,
            'must-be-taken'     => true
        ]);
    }
}
