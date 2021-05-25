<?php

namespace App\Console\Commands\KDashboard;

use App\Models\Character;
use App\Models\KDashboard\Character as KCharacter;
use Illuminate\Console\Command;

class ImportCharacters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:character';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the characters from the KDashboard database.';

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
        $kCharacters = KCharacter::all();
        $oldCount = Character::count('id');

        $this->info('Total old characters: ' . $oldCount);

        $this->withProgressBar($kCharacters, function (KCharacter $kCharacter) {
            $character = Character::where([
                ['mal_id', $kCharacter->id],
            ])->first();

            if ($character) {
                return;
            }

            $japaneseName = [];
            if (!empty($kCharacter->japanese_name)) {
                $japaneseName = [
                    'ja' => [
                        'name' => $kCharacter->japanese_name,
                        'about' => '',
                    ],
                ];
            }

            Character::create(array_merge($japaneseName, [
                'mal_id'    => $kCharacter->id,
                'nicknames' => empty($kCharacter->nickname) ? null : explode(', ', $kCharacter->nickname),
                'name'      => $kCharacter->name,
                'about'     => $kCharacter->about,
                'image'     => $kCharacter->image_url,
            ]));
        });

        $newCount = Character::count('id');

        $this->newLine();
        $this->info('Total new characters added: ' . $newCount - $oldCount);
        $this->info('Total characters: ' . $newCount);

        return 1;
    }
}
