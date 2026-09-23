<?php

namespace App\Console\Commands\Fixers;

use App\Models\MediaStaff;
use App\Models\RecapItem;
use App\Models\StaffRole;
use DB;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Pulse;
use Throwable;

class StaffRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:staff_roles
                            {--last-valid-id=180 : The ID of the last correctly named staff role}
                            {--dry-run : Print the plan without changing anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merges staff roles split apart by the MyAnimeList scraper into their real roles';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Throwable
     */
    public function handle(): int
    {
        Pulse::stopRecording();
        Telescope::stopRecording();

        $lastValidID = (int) $this->option('last-valid-id');
        [$targets, $renames] = $this->plan($lastValidID);

        $remapIDs = array_keys(array_filter($targets, fn ($targetID) => $targetID !== null));
        $deleteIDs = array_keys(array_filter($targets, fn ($targetID) => $targetID === null));

        $this->info('Roles merged into existing roles: ' . count($remapIDs));
        $this->info('Roles renamed to their real name: ' . count($renames));
        $this->info('Fragment roles removed with their credits: ' . count($deleteIDs));
        $this->info('Credits to merge: ' . MediaStaff::withTrashed()->whereIn('staff_role_id', $remapIDs)->count());
        $this->info('Fragment credits to remove: ' . MediaStaff::withTrashed()->whereIn('staff_role_id', $deleteIDs)->count());

        if (!empty($renames)) {
            $this->table(['ID', 'Current name', 'New name'], collect($renames)->map(function ($newName, $roleID) {
                return [$roleID, StaffRole::find($roleID)?->name, $newName];
            })->values()->all());
        }

        if ($this->option('dry-run')) {
            $this->comment('Dry run: nothing was changed.');
            return Command::SUCCESS;
        }

        foreach ($renames as $roleID => $name) {
            StaffRole::where('id', '=', $roleID)
                ->update(['name' => $name]);
        }

        $this->mergeCredits($targets);

        foreach (array_chunk($deleteIDs, 1000) as $deleteIDsChunk) {
            MediaStaff::withTrashed()
                ->whereIn('staff_role_id', $deleteIDsChunk)
                ->forceDelete();
        }

        $this->mergeRecapItems($targets);

        foreach (array_chunk(array_merge($remapIDs, $deleteIDs), 1000) as $roleIDsChunk) {
            StaffRole::whereIn('id', $roleIDsChunk)
                ->delete();
        }

        $this->info('Staff roles fixed.');

        Pulse::startRecording();
        Telescope::startRecording();

        return Command::SUCCESS;
    }

    /**
     * The real role of every broken staff role, and the broken roles renamed to their real name.
     *
     * @param int $lastValidID
     *
     * @return array
     */
    protected function plan(int $lastValidID): array
    {
        $roles = StaffRole::orderBy('id')
            ->get(['id', 'name']);
        $roleIDsByName = [];

        foreach ($roles as $role) {
            if ($role->id <= $lastValidID) {
                $roleIDsByName[mb_strtolower($role->name)] = $role->id;
            }
        }

        $brokenRoles = $roles->where('id', '>', $lastValidID);
        $cleanNames = [];
        $renames = [];

        // Prefixes before an unclosed parenthesis are real roles
        foreach ($brokenRoles as $role) {
            if (!str_contains($role->name, '(')) {
                continue;
            }

            $cleanName = trim(strstr($role->name, '(', true));
            $cleanNames[$role->id] = $cleanName;
            $key = mb_strtolower($cleanName);

            if ($cleanName === '' || isset($roleIDsByName[$key])) {
                continue;
            }

            $exactRole = $brokenRoles->first(fn (StaffRole $brokenRole) => $brokenRole->name === $cleanName);
            $roleIDsByName[$key] = $exactRole?->id ?? $role->id;

            if ($exactRole === null) {
                $renames[$role->id] = $cleanName;
            }
        }

        $targets = [];

        foreach ($brokenRoles as $role) {
            $cleanName = $cleanNames[$role->id] ?? trim(str_replace(')', '', $role->name));
            $targetID = $cleanName === '' ? null : ($roleIDsByName[mb_strtolower($cleanName)] ?? null);

            if ($targetID === $role->id) {
                continue;
            }

            $targets[$role->id] = $targetID;
        }

        return [$targets, $renames];
    }

    /**
     * Moves the credits of the broken roles to their real roles.
     *
     * @param array $targets
     *
     * @return void
     * @throws Throwable
     */
    protected function mergeCredits(array $targets): void
    {
        $remapIDs = array_keys(array_filter($targets, fn ($targetID) => $targetID !== null));
        $mergedCount = 0;

        foreach (array_chunk($remapIDs, 1000) as $remapIDsChunk) {
            MediaStaff::withTrashed()
                ->whereIn('staff_role_id', $remapIDsChunk)
                ->chunkById(1000, function ($mediaStaff) use ($targets, &$mergedCount) {
                    DB::transaction(function () use ($mediaStaff, $targets, &$mergedCount) {
                        $credits = MediaStaff::withTrashed()
                            ->whereIn('person_id', $mediaStaff->pluck('person_id')->unique())
                            ->whereIn('staff_role_id', $mediaStaff->map(fn (MediaStaff $credit) => $targets[$credit->staff_role_id])->unique())
                            ->toBase()
                            ->get(['model_type', 'model_id', 'person_id', 'staff_role_id'])
                            ->mapWithKeys(fn ($credit) => [$credit->model_type . ':' . $credit->model_id . ':' . $credit->person_id . ':' . $credit->staff_role_id => true])
                            ->all();
                        $updates = [];
                        $duplicateIDs = [];

                        foreach ($mediaStaff as $credit) {
                            $targetID = $targets[$credit->staff_role_id];
                            $key = $credit->model_type . ':' . $credit->model_id . ':' . $credit->person_id . ':' . $targetID;

                            if (isset($credits[$key])) {
                                $duplicateIDs[] = $credit->id;
                                continue;
                            }

                            $credits[$key] = true;
                            $updates[$targetID][] = $credit->id;
                        }

                        if (!empty($duplicateIDs)) {
                            MediaStaff::withTrashed()
                                ->whereIn('id', $duplicateIDs)
                                ->forceDelete();
                        }

                        foreach ($updates as $targetID => $creditIDs) {
                            MediaStaff::withTrashed()
                                ->whereIn('id', $creditIDs)
                                ->update(['staff_role_id' => $targetID]);
                        }

                        $mergedCount += $mediaStaff->count();
                    });

                    $this->line('Merged ' . $mergedCount . ' credits');
                });
        }
    }

    /**
     * Points recap items at the real roles of their broken roles.
     *
     * @param array $targets
     *
     * @return void
     */
    protected function mergeRecapItems(array $targets): void
    {
        $roleIDsByTarget = [];

        foreach ($targets as $roleID => $targetID) {
            $roleIDsByTarget[$targetID ?? 0][] = $roleID;
        }

        foreach ($roleIDsByTarget as $targetID => $roleIDs) {
            foreach (array_chunk($roleIDs, 1000) as $roleIDsChunk) {
                RecapItem::where('role_type', '=', StaffRole::class)
                    ->whereIn('role_id', $roleIDsChunk)
                    ->update($targetID === 0 ? [
                        'role_type' => null,
                        'role_id' => null,
                    ] : [
                        'role_id' => $targetID,
                    ]);
            }
        }
    }
}
