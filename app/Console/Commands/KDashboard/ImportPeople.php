<?php

namespace App\Console\Commands\KDashboard;

use App\Models\KDashboard\People as KPeople;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportPeople extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:people';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the people from the KDashboard database';

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
        $kPeople = KPeople::all();
        $oldCount = Person::count('id');

        $this->info('Total old people: ' . $oldCount);

        $this->withProgressBar($kPeople, function (KPeople $kPerson) {
            $person = Person::where([
                ['mal_id', $kPerson->id],
            ])->first();

            if ($person) {
                return;
            }

            $name = explode(', ', $kPerson->name);
            $firstName = $name[0];
            $lastName = $name[1] ?? '';
            $birthDate = null;

            if ($kPerson->birthday_day != 0 && $kPerson->birthday_month != 0) {
                $birthDate = $kPerson->birthday_year . '-' . $kPerson->birthday_month . '-' . $kPerson->birthday_day;
            }

            Person::create([
                'mal_id'            => $kPerson->id,
                'first_name'        => $firstName,
                'last_name'         => $lastName,
                'given_name'        => $kPerson->given_name,
                'family_name'       => $kPerson->family_name,
                'alternative_names' => explode(', ', $kPerson->alternative_name),
                'about'             => $kPerson->more,
                'birth_date'        => empty($birthDate) ? null : Carbon::parse($birthDate),
                'image'             => $kPerson->image_url,
                'website_urls'      => empty($kPerson->website) ? null : explode(', ', $kPerson->website),
            ]);
        });

        $newCount = Person::count('id');

        $this->newLine();
        $this->info('Total new people added: ' . $newCount - $oldCount);
        $this->info('Total people: ' . $newCount);

        return 1;
    }
}
