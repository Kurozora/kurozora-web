<?php

namespace Tests\API;

use App\Enums\ImportBehavior;
use App\Enums\ImportService;
use App\Enums\UserLibraryKind;
use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Notifications\LibraryImportFinished;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestMultipleAnime;
use Tests\Traits\ProvidesTestUser;

class LibraryImportTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser, ProvidesTestMultipleAnime;

    /**
     * The MAL XML content.
     *
     * @var string
     */
    protected static string $xmlContent = <<<XML
    <myanimelist>
        <anime>
            <series_animedb_id>6076</series_animedb_id>
            <series_title><![CDATA['Eiji']]></series_title>
            <series_type>OVA</series_type>
            <series_episodes>1</series_episodes>
            <my_id>0</my_id>
            <my_watched_episodes>1</my_watched_episodes>
            <my_start_date>0000-00-00</my_start_date>
            <my_finish_date>0000-00-00</my_finish_date>
            <my_rated></my_rated>
            <my_score>10</my_score>
            <my_storage></my_storage>
            <my_storage_value>0.00</my_storage_value>
            <my_status>Completed</my_status>
            <my_comments><![CDATA[]]></my_comments>
            <my_times_watched>0</my_times_watched>
            <my_rewatch_value></my_rewatch_value>
            <my_priority>LOW</my_priority>
            <my_tags><![CDATA[]]></my_tags>
            <my_rewatching>0</my_rewatching>
            <my_rewatching_ep>0</my_rewatching_ep>
            <my_discuss>1</my_discuss>
            <my_sns>default</my_sns>
            <update_on_import>0</update_on_import>
        </anime>
    
        <anime>
            <series_animedb_id>2928</series_animedb_id>
            <series_title><![CDATA[.hack//G.U. Returner]]></series_title>
            <series_type>OVA</series_type>
            <series_episodes>1</series_episodes>
            <my_id>0</my_id>
            <my_watched_episodes>1</my_watched_episodes>
            <my_start_date>0000-00-00</my_start_date>
            <my_finish_date>0000-00-00</my_finish_date>
            <my_rated></my_rated>
            <my_score>10</my_score>
            <my_storage></my_storage>
            <my_storage_value>0.00</my_storage_value>
            <my_status>Dropped</my_status>
            <my_comments><![CDATA[]]></my_comments>
            <my_times_watched>0</my_times_watched>
            <my_rewatch_value></my_rewatch_value>
            <my_priority>HIGH</my_priority>
            <my_tags><![CDATA[]]></my_tags>
            <my_rewatching>0</my_rewatching>
            <my_rewatching_ep>0</my_rewatching_ep>
            <my_discuss>0</my_discuss>
            <my_sns>default</my_sns>
            <update_on_import>0</update_on_import>
        </anime>
    
        <anime>
            <series_animedb_id>3269</series_animedb_id>
            <series_title><![CDATA[.hack//G.U. Trilogy]]></series_title>
            <series_type>Movie</series_type>
            <series_episodes>1</series_episodes>
            <my_id>0</my_id>
            <my_watched_episodes>1</my_watched_episodes>
            <my_start_date>0000-00-00</my_start_date>
            <my_finish_date>0000-00-00</my_finish_date>
            <my_rated></my_rated>
            <my_score>10</my_score>
            <my_storage></my_storage>
            <my_storage_value>0.00</my_storage_value>
            <my_status>Plan to Watch</my_status>
            <my_comments><![CDATA[]]></my_comments>
            <my_times_watched>0</my_times_watched>
            <my_rewatch_value></my_rewatch_value>
            <my_priority>HIGH</my_priority>
            <my_tags><![CDATA[]]></my_tags>
            <my_rewatching>0</my_rewatching>
            <my_rewatching_ep>0</my_rewatching_ep>
            <my_discuss>0</my_discuss>
            <my_sns>default</my_sns>
            <update_on_import>0</update_on_import>
        </anime>
    
        <anime>
            <series_animedb_id>4469</series_animedb_id>
            <series_title><![CDATA[.hack//G.U. Trilogy: Parody Mode]]></series_title>
            <series_type>Special</series_type>
            <series_episodes>1</series_episodes>
            <my_id>0</my_id>
            <my_watched_episodes>1</my_watched_episodes>
            <my_start_date>0000-00-00</my_start_date>
            <my_finish_date>0000-00-00</my_finish_date>
            <my_rated></my_rated>
            <my_score>10</my_score>
            <my_storage></my_storage>
            <my_storage_value>0.00</my_storage_value>
            <my_status>On-Hold</my_status>
            <my_comments><![CDATA[]]></my_comments>
            <my_times_watched>0</my_times_watched>
            <my_rewatch_value></my_rewatch_value>
            <my_priority>HIGH</my_priority>
            <my_tags><![CDATA[]]></my_tags>
            <my_rewatching>0</my_rewatching>
            <my_rewatching_ep>0</my_rewatching_ep>
            <my_discuss>0</my_discuss>
            <my_sns>default</my_sns>
            <update_on_import>0</update_on_import>
        </anime>
    
        <anime>
            <series_animedb_id>454</series_animedb_id>
            <series_title><![CDATA[.hack//Gift]]></series_title>
            <series_type>OVA</series_type>
            <series_episodes>1</series_episodes>
            <my_id>0</my_id>
            <my_watched_episodes>1</my_watched_episodes>
            <my_start_date>0000-00-00</my_start_date>
            <my_finish_date>0000-00-00</my_finish_date>
            <my_rated></my_rated>
            <my_score>10</my_score>
            <my_storage></my_storage>
            <my_storage_value>0.00</my_storage_value>
            <my_status>Completed</my_status>
            <my_comments><![CDATA[]]></my_comments>
            <my_times_watched>0</my_times_watched>
            <my_rewatch_value></my_rewatch_value>
            <my_priority>HIGH</my_priority>
            <my_tags><![CDATA[]]></my_tags>
            <my_rewatching>0</my_rewatching>
            <my_rewatching_ep>0</my_rewatching_ep>
            <my_discuss>0</my_discuss>
            <my_sns>default</my_sns>
            <update_on_import>0</update_on_import>
        </anime>
    
        <anime>
            <series_animedb_id>1143</series_animedb_id>
            <series_title><![CDATA[.hack//Intermezzo]]></series_title>
            <series_type>Special</series_type>
            <series_episodes>1</series_episodes>
            <my_id>0</my_id>
            <my_watched_episodes>1</my_watched_episodes>
            <my_start_date>0000-00-00</my_start_date>
            <my_finish_date>0000-00-00</my_finish_date>
            <my_rated></my_rated>
            <my_score>10</my_score>
            <my_storage></my_storage>
            <my_storage_value>0.00</my_storage_value>
            <my_status>Watching</my_status>
            <my_comments><![CDATA[]]></my_comments>
            <my_times_watched>0</my_times_watched>
            <my_rewatch_value></my_rewatch_value>
            <my_priority>HIGH</my_priority>
            <my_tags><![CDATA[]]></my_tags>
            <my_rewatching>0</my_rewatching>
            <my_rewatching_ep>0</my_rewatching_ep>
            <my_discuss>0</my_discuss>
            <my_sns>default</my_sns>
            <update_on_import>0</update_on_import>
        </anime>
    </myanimelist>
    XML;

    /**
     * User can import MAL library with overwrite behavior.
     *
     * @return void
     */
    #[Test]
    function user_can_import_mal_library_with_overwrite_behavior(): void
    {
        // Attach anime with id 21
        $anime = Anime::firstWhere('mal_id', 21);
        $this->user->track($anime, UserLibraryStatus::InProgress());

        // Prepare import file
        $uploadFile = UploadedFile::fake()->createWithContent('animelist_1623616958_-_3667065.xml', self::$xmlContent);

        // Make sure the anime has been attached to the user
        $this->assertTrue($this->user->hasTracked($anime));

        // Expect a job is dispatched
        Notification::fake();

        // Request import
        $response = $this->auth()->json('POST', route('api.me.library.import'), [
            'library' => UserLibraryKind::Anime,
            'service' => ImportService::MAL,
            'file' => $uploadFile,
            'behavior' => ImportBehavior::Overwrite,
        ]);
        $response->assertSuccessfulAPIResponse();

        // Assert notification was sent
        Notification::hasSent($this->user, LibraryImportFinished::class);

        // Assert anime has been imported in user's library
        $this->assertEquals(6, $this->user->whereTracked(Anime::class)->count());

        // Assert the anime we added in the beginning is gone
        $this->assertNull($this->user->whereTracked(Anime::class)->firstWhere('mal_id', 21));
    }

    /**
     * User can import MAL library with merge behavior.
     *
     * @return void
     */
    #[Test]
    function user_can_import_mal_library_with_merge_behavior(): void
    {
        // Attach anime with id 21
        $anime = Anime::firstWhere('mal_id', 21);
        $this->user->track($anime, UserLibraryStatus::InProgress());

        // Prepare import file
        $uploadFile = UploadedFile::fake()->createWithContent('animelist_1623616958_-_3667065.xml', self::$xmlContent);

        // Make sure the anime has been attached to the user
        $this->assertEquals(1, $this->user->whereTracked(Anime::class)->count());

        // Expect a job is dispatched
        Notification::fake();

        // Request import
        $response = $this->auth()->json('POST', route('api.me.library.import'), [
            'library' => UserLibraryKind::Anime,
            'service' => ImportService::MAL,
            'file' => $uploadFile,
            'behavior' => ImportBehavior::Merge,
        ]);
        $response->assertSuccessfulAPIResponse();

        // Assert notification was sent
        Notification::hasSent($this->user, LibraryImportFinished::class);

        // Assert anime has been imported in user's library
        $this->assertEquals(7, $this->user->whereTracked(Anime::class)->count());

        // Assert the anime we added in the beginning is gone
        $this->assertNotNull($this->user->whereTracked(Anime::class)->firstWhere('mal_id', 21));
    }
}
