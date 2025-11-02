<?php

namespace App\Console\Commands\Fixers;

use App\Models\MediaSong;
use DB;
use Illuminate\Console\Command;
use Throwable;

class MediaSongOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:media_song_order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the order of songs belonging to a specific media based on the `episodes` field.';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Throwable
     */
    public function handle(): int
    {
        // Get only groups with duplicate positions
        $groups = MediaSong::select('model_type', 'model_id', 'type')
            ->groupBy('model_type', 'model_id', 'type', 'position')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->map(fn ($g) => [
                'model_type' => $g->model_type,
                'model_id'   => $g->model_id,
                'type'       => $g->type,
            ])
            ->unique()
            ->values();

        $this->info('Found ' . $groups->count() . ' groups with duplicate positions.');

        $bar = $this->output->createProgressBar($groups->count());
        $bar->start();

        $groups->chunk(10)->each(function ($chunk) use ($bar) {
            DB::transaction(function () use ($chunk, $bar) {
                foreach ($chunk as $group) {
                    $songs = MediaSong::where('model_type', '=', $group['model_type'])
                        ->where('model_id', '=', $group['model_id'])
                        ->where('type', '=', $group['type'])
                        ->get();

                    // Parse episodes into min episode for sorting
                    $songs = $songs->map(function ($song) {
                        $minEpisode = collect(explode(',', $song->episodes))
                            ->map(function ($part) {
                                $part = trim($part);
                                if (str_contains($part, '-')) {
                                    [$start,] = explode('-', $part, 2);
                                    return (int) $start;
                                }
                                return (int) $part;
                            })
                            ->filter()
                            ->min();

                        $song->min_episode = $minEpisode ?: PHP_INT_MAX;
                        return $song;
                    });

                    // Sort and reassign positions
                    $sorted = $songs->sortBy('min_episode')->values();
                    foreach ($sorted as $index => $song) {
                        $newPosition = $index + 1;
                        if ($song->position !== $newPosition) {
                            unset($song->min_episode);
                            $song->update(['position' => $newPosition]);
                        }
                    }

                    $bar->advance();
                }
            });
        });

        $bar->finish();
        $this->newLine();
        $this->info('All duplicate positions have been fixed!');

        return Command::SUCCESS;
    }
}
