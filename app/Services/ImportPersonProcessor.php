<?php

namespace App\Services;

use App\Models\KDashboard\People as KPerson;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ImportPersonProcessor
{
    /**
     * Processes the job.
     *
     * @param Collection|KPerson[] $kPeople
     * @return void
     */
    public function process(Collection|array $kPeople)
    {
        foreach ($kPeople as $kPerson) {
            $person = Person::where([
                ['mal_id', $kPerson->id],
            ])->first();

            if (empty($person)) {
                $name = explode(', ', $kPerson->name);
                $firstName = $name[0];
                $lastName = $name[1] ?? '';
                $birthDate = null;

                if ($kPerson->birthday_day != 0 && $kPerson->birthday_month != 0) {
                    $birthDate = $kPerson->birthday_year . '-' . $kPerson->birthday_month . '-' . $kPerson->birthday_day;
                }

                Person::create([
                    'mal_id' => $kPerson->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'given_name' => $kPerson->given_name,
                    'family_name' => $kPerson->family_name,
                    'alternative_names' => explode(', ', $kPerson->alternative_name),
                    'about' => $kPerson->more,
                    'birth_date' => empty($birthDate) ? null : Carbon::parse($birthDate),
                    'image' => $kPerson->image_url,
                    'website_urls' => empty($kPerson->website) ? null : explode(', ', $kPerson->website),
                ]);
            }
        }
    }
}
