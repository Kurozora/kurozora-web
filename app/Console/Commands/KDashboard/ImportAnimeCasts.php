<?php

namespace App\Console\Commands\KDashboard;

use App\Models\Anime;
use App\Models\AnimeCast;
use App\Models\CastRole;
use App\Models\Character;
use App\Models\KDashboard\AnimeCharacter as KAnimeCast;
use App\Models\Language;
use App\Models\Person;
use Illuminate\Console\Command;

class ImportAnimeCasts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:anime-cast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the anime casts from the KDashboard database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $oldCount = AnimeCast::count('id');
        $kAnimeCasts = KAnimeCast::skip($oldCount)->get();
        $this->info('Total old anime casts: ' . $oldCount);

        $this->withProgressBar($kAnimeCasts, function (KAnimeCast $kAnimeCast) {
            $kCastRole = match ($kAnimeCast->role) {
                'main' => 'Protagonist',
                default => 'Supporting Character'
            };
            $kLanguage = $kAnimeCast->language ? match ($kAnimeCast->language->language) {
                'Mandarin' => 'Chinese',
                'Brazilian' => 'Portuguese',
                default => $kAnimeCast->language->language
            } : null;

            $animeId = Anime::firstWhere('mal_id', $kAnimeCast->anime_id)->id;
            $characterId = Character::firstWhere('mal_id' , $kAnimeCast->character_id)->id;
            $personId = $kAnimeCast->people_id ? Person::firstWhere('mal_id', $kAnimeCast->people_id)->id : null;
            $castRoleId = CastRole::firstWhere('name', $kCastRole)->id;
            $languageId = $kLanguage ? Language::firstWhere('name', $kLanguage)->id : null;

            $animeCast = AnimeCast::where([
                ['anime_id', $animeId],
                ['character_id', $characterId],
                ['person_id', $personId],
                ['cast_role_id', $castRoleId],
                ['language_id', $languageId],
            ])->first();

            if ($animeCast) {
                return;
            }

            AnimeCast::create([
                'anime_id' => $animeId,
                'character_id' => $characterId,
                'person_id' => $personId,
                'cast_role_id' => $castRoleId,
                'language_id' => $languageId,
            ]);
        });

        $newCount = AnimeCast::count('id');

        $this->newLine();
        $this->info('Total new anime casts added: ' . $newCount - $oldCount);
        $this->info('Total anime casts: ' . $newCount);

        return 1;
    }
}
