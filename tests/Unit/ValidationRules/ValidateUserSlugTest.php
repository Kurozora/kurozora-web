<?php

namespace Tests\Unit\ValidationRules;

use App\Models\User;
use App\Rules\ValidateUserSlug;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidateUserSlugTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A collection of slug addresses that are valid.
     *
     * @var Collection $validUserSlug
     */
    private Collection $validUserSlug;

    /**
     * A collection of slug addresses that are invalid.
     *
     * @var Collection $invalidUserSlug
     */
    private Collection $invalidUserSlug;

    /**
     * The user slug validation rule.
     *
     * @var ValidateUserSlug $rule
     */
    private ValidateUserSlug $rule;

    public function setUp(): void
    {
        parent::setUp();

        $this->rule = new ValidateUserSlug();

        // Source: https://gist.github.com/cjaoude/fd9910626629b53c4d25
        $this->validUserSlug = collect([
            'abc',
            'Siesta2349',
            'campfire',
            'Iron_Pug',
            'electric-mouse',
        ]);

        $this->invalidUserSlug = collect([
            '',
            '1',
            '@example.com',
            'this is a very long slug that exceeds the max char limit or at least it should',
            'Iron Pug',
        ]);
    }

    /**
     * Valid slug pass.
     *
     * @return void
     */
    #[Test]
    public function valid_slug_pass()
    {
        foreach ($this->validUserSlug as $slug) {
            $message = '';

            $this->rule->validate('slug', $slug, function ($error) use (&$message) {
                $message = $error;
            });

            $this->assertEmpty($message, $slug . ' did not pass. ' . $message);
        }
    }

    /**
     * Invalid slug dont pass.
     *
     * @return void
     */
    #[Test]
    public function invalid_slug_dont_pass()
    {
        foreach ($this->invalidUserSlug as $slug) {
            $message = '';

            $this->rule->validate('slug', $slug, function ($error) use (&$message) {
                $message = $error;
            });

            $this->assertNotEmpty($message, $slug . ' did not pass. ' . $message);
        }
    }

    /**
     * Slug is taken.
     *
     * @return void
     */
    #[Test]
    public function slug_is_taken()
    {
        $this->rule = new ValidateUserSlug();

        // Take a random valid slug
        $slug = $this->validUserSlug->random();

        // The slug should pass, because it is available
        $message = '';

        $this->rule->validate('slug', $slug, function ($error) use (&$message) {
            $message = $error;
        });

        $this->assertEmpty($message, $slug . ' did not pass. ' . $message);

        // Create a user account with the slug
        User::factory()->create(['slug' => $slug]);

        // The slug should not pass, because it is taken
        $message = '';

        $this->rule->validate('slug', $slug, function ($error) use (&$message) {
            $message = $error;
        });

        $this->assertNotEmpty($message, $slug . ' did not pass. ' . $message);
    }
}
