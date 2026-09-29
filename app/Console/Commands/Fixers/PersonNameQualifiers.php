<?php

namespace App\Console\Commands\Fixers;

use App\Console\Commands\Importers\ImportVisualNovels;
use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Laravel\Telescope\Telescope;
use Pulse;

class PersonNameQualifiers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:person_name_qualifiers
                            {--dry-run : Print the plan without changing anything.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes the disambiguation qualifier from person names.';

    /**
     * The name columns a qualifier can end up in.
     *
     * @var string[]
     */
    protected const array COLUMNS = ['first_name', 'last_name', 'given_name', 'family_name'];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $isDryRun = (bool) $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Dry run. Nothing will be changed.');
        }

        $query = Person::withoutGlobalScopes()->where(function ($person) {
            foreach (self::COLUMNS as $column) {
                $person->orWhere($column, 'like', '%(%)%');
            }
        });

        $fixed = 0;

        foreach ($query->cursor() as $person) {
            $changes = [];

            foreach (self::COLUMNS as $column) {
                $stripped = $this->strip($person->{$column});

                if ($stripped !== $person->{$column}) {
                    $changes[$column] = $stripped;
                }
            }

            if (empty($changes)) {
                continue;
            }

            // A qualifier standing alone as the given name leaves it empty.
            if (array_key_exists('first_name', $changes) && $changes['first_name'] === '') {
                $last = array_key_exists('last_name', $changes) ? $changes['last_name'] : $person->last_name;

                if (!empty($last)) {
                    $changes['first_name'] = $last;
                    $changes['last_name'] = null;
                }
            }

            $fixed++;

            foreach ($changes as $column => $value) {
                $this->line(sprintf('  %-8d %-12s %-28s -> %s', $person->id, $column, $person->{$column}, $value ?? 'NULL'));
            }

            if ($isDryRun) {
                continue;
            }

            $person->slug = null;
            $person->fill($changes)->save();
        }

        $this->newLine();
        $this->info(($isDryRun ? 'Would fix: ' : 'Fixed: ') . $fixed . ' person/people.');

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * Remove a known qualifier from the end of a name.
     *
     * @param string|null $name
     * @return string|null
     */
    protected function strip(?string $name): ?string
    {
        if (empty($name) || !preg_match('/\s*\(([^)]*)\)\s*$/u', $name, $matches)) {
            return $name;
        }

        if (!in_array(Str::lower(trim($matches[1])), ImportVisualNovels::NAME_QUALIFIERS, true)) {
            return $name;
        }

        return trim(Str::beforeLast($name, $matches[0]));
    }
}
