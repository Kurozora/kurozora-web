<?php

namespace Tests\API\UserLibrary;

use App\Enums\DayOfWeek;
use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Models\MediaRating;
use App\Models\MediaType;
use App\Models\Source;
use App\Models\Status;
use App\Models\TvRating;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

class LibrarySortingTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->addTestingAnimeToLibrary($this->user);
    }

    /**
     * User can sort their library based on title.
     *
     * @return void
     */
    #[Test]
    function user_can_sort_their_library_based_on_title(): void
    {
        // Send the request and sort by title ascending
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'status' => UserLibraryStatus::InProgress,
            'sort' => 'title(asc)',
        ]));

        $this->assertMatchesJsonSnapshot($response->json()['data']);

        // Send the request and sort by title descending
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'status' => UserLibraryStatus::InProgress,
            'sort' => 'title(desc)',
        ]));

        $this->assertMatchesJsonSnapshot($response->json()['data']);
    }

    /**
     * User can sort their library based on age.
     *
     * @return void
     */
    #[Test]
    function user_can_sort_their_library_based_on_age(): void
    {
        // Send the request and sort by age newest
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'status' => UserLibraryStatus::InProgress,
            'sort' => 'age(newest)',
        ]));

        $this->assertMatchesJsonSnapshot($response->json()['data']);

        // Send the request and sort by age oldest
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'status' => UserLibraryStatus::InProgress,
            'sort' => 'age(oldest)',
        ]));

        $this->assertMatchesJsonSnapshot($response->json()['data']);
    }

    /**
     * User can sort their library based on rating.
     *
     * @return void
     */
    #[Test]
    function user_can_sort_their_library_based_on_rating(): void
    {
        // Send the request and sort by rating best
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'status' => UserLibraryStatus::InProgress,
            'sort' => 'rating(best)',
        ]));

        $this->assertMatchesJsonSnapshot($response->json()['data']);

        // Send the request and sort by rating worst
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'status' => UserLibraryStatus::InProgress,
            'sort' => 'rating(worst)',
        ]));

        $this->assertMatchesJsonSnapshot($response->json()['data']);
    }

    /**
     * User can sort their library based on their own giving rating.
     *
     * @return void
     */
    #[Test]
    function user_can_sort_their_library_based_on_their_own_given_rating(): void
    {
        // Send the request and sort by my rating best
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'status' => UserLibraryStatus::InProgress,
            'sort' => 'my-rating(best)',
        ]));

        $this->assertMatchesJsonSnapshot($response->json()['data']);

        // Send the request and sort by my rating worst
        $response = $this->auth()->getJson(route('api.me.library.index', [
            'status' => UserLibraryStatus::InProgress,
            'sort' => 'my-rating(worst)',
        ]));

        $this->assertMatchesJsonSnapshot($response->json()['data']);
    }

    /**
     * Adds the testing Anime to the user's library.
     *
     * @param User $user
     */
    private function addTestingAnimeToLibrary(User $user): void
    {
        $tvRating = TvRating::factory()->create([
            'name' => 'z',
            'description' => 'Eveniet maxime commodi eum ut explicabo officia ipsam doloribus.',
        ]);
        $mediaType = MediaType::factory()->create([
            'type' => 'anime',
            'name' => 'Rafaela Hegmann',
            'description' => 'Aliquid placeat libero qui eos atque omnis.',
        ]);
        $source = Source::factory()->create([
            'name' => 'Sherman Zieme PhD',
            'description' => 'Provident labore et dolorem qui.',
        ]);
        $status = Status::factory()->create([
            'type' => 'anime',
            'name' => 'iusto minus',
            'description' => 'Mollitia totam nulla ut tenetur saepe soluta dolor.',
            'color' => '#b01b3a',
        ]);

        // Add the first Anime
        $anime1 = Anime::factory()->create([
            'id' => 1,
            'slug' => 'awesome-show',
            'original_title' => 'Awesome Show',
            'title' => 'Awesome Show',
            'synopsis' => 'A very awesome show.',
            'tagline' => 'Eos eos ad quaerat qui corporis placeat iure.',
            'synonym_titles' => [
                'Est quia est vitae consequuntur.',
                'Deserunt veniam pariatur eligendi cumque maiores.',
                'Minima laboriosam minima praesentium nihil officia.'
            ],
            'tv_rating_id' => $tvRating->id,
            'media_type_id' => $mediaType->id,
            'source_id' => $source->id,
            'status_id' => $status->id,
            'started_at' => 67046400,
            'ended_at' => 73094400,
            'duration' => 1440,
            'air_day' => DayOfWeek::Sunday,
            'air_time' => '17:00',
            'is_nsfw' => false,
            'copyright' => '® 2012 Steuber Group',
            'created_at' => now(),
        ]);
        $anime1->mediaStat()->update([
            'rating_average' => 2.5,
        ]);
        $this->user->track($anime1, UserLibraryStatus::InProgress());

        MediaRating::create([
            'model_type' => Anime::class,
            'model_id' => $anime1->id,
            'user_id' => $user->id,
            'rating' => 3.0,
        ]);

        // Add the second Anime
        $anime2 = Anime::factory()->create([
            'id' => 2,
            'slug' => 'be-a-good-person',
            'original_title' => 'Be a good person',
            'title' => 'Be a good person!',
            'synopsis' => 'A story about being a good person.',
            'tagline' => 'Odio nesciunt vel eveniet esse.',
            'synonym_titles' => [
                'Est quia est vitae consequuntur.',
                'Deserunt veniam pariatur eligendi cumque maiores.',
                'Minima laboriosam minima praesentium nihil officia.'
            ],
            'tv_rating_id' => $tvRating->id,
            'media_type_id' => $mediaType->id,
            'source_id' => $source->id,
            'status_id' => $status->id,
            'started_at' => 67046400,
            'ended_at' => 73094400,
            'duration' => 1440,
            'air_day' => DayOfWeek::Wednesday,
            'air_time' => '16:30',
            'is_nsfw' => true,
            'copyright' => '℗ 2018 Abernathy-Daniel',
            'created_at' => now()->subDay(),
        ]);
        $anime2->mediaStat()->update([
            'rating_average' => 4.0,
        ]);
        $this->user->track($anime2, UserLibraryStatus::InProgress());

        MediaRating::create([
            'model_type' => Anime::class,
            'model_id' => $anime2->id,
            'user_id' => $user->id,
            'rating' => 1.2,
        ]);
    }
}
